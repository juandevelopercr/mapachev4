<?php

namespace backend\controllers;

use Yii;
use kartik\mpdf\Pdf;
use yii\helpers\Url;
use yii\db\Exception;
use yii\web\Response;
use common\models\User;
use Mpdf\QrCode\Output;
use Mpdf\QrCode\QrCode;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use backend\components\ApiBCCR;
use common\models\EnviarEmailForm;
use common\models\GlobalFunctions;
use yii\web\NotFoundHttpException;
use backend\models\settings\Issuer;
use backend\models\business\Invoice;
use backend\models\business\Product;
use backend\models\settings\Setting;
use common\components\ApiV43\ApiXML;
use backend\models\business\Customer;
use backend\models\business\Adjustment;
use backend\models\business\CreditNote;
use common\components\ApiV43\ApiAccess;
use backend\models\business\ItemInvoice;
use backend\models\nomenclators\Currency;
use backend\models\business\ItemCreditNote;
use backend\models\business\CreditNoteSearch;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\ConditionSale;
use common\components\ApiV43\ApiEnvioHacienda;
use backend\models\nomenclators\UtilsConstants;
use common\components\ApiV43\ApiFirmadoHacienda;
use backend\models\business\ItemCreditNoteSearch;
use common\components\ApiV43\ApiConsultaHacienda;
use backend\models\business\PaymentMethodHasInvoice;
use backend\models\business\PaymentMethodHasCreditNote;

/**
 * CreditNoteTpvController implements the CRUD actions for CreditNote model.
 */
class CreditNoteTpvController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'multiple_delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all CreditNote models.
     * @return mixed
     */
    public function actionIndex()
    {
        $is_point_sale = 1;
        $searchModel = new CreditNoteSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $is_point_sale);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CreditNote model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModelItems = new ItemCreditNoteSearch(['credit_note_id' => $id]);
        $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);

        return $this->render('view', [
            'model' => $model,
            'dataProviderItems' => $dataProviderItems,
        ]);
    }

    public function actionCreate($invoice_id)
    {
        $model = new CreditNote();
        $model->LoadDefaultValues();
        $invoice = Invoice::findOne($invoice_id);
        if (count($invoice->itemInvoices) <= 0)
            GlobalFunctions::addFlashMessage('danger', 'La factura electrónica no tiene ningún item asociado');

        $model->attributes = $invoice->attributes;

        $model->credit_note_type = UtilsConstants::CREDIT_NOTE_TYPE_TOTAL;
        $model->status = UtilsConstants::INVOICE_STATUS_PENDING;
        $model->status_hacienda = UtilsConstants::HACIENDA_STATUS_NOT_SENT;
        $model->ready_to_send_email = 0;
        $model->email_sent = 0;
        $model->emission_date = date('Y-m-d H:i:s');
        $model->response_xml = '';
        $model->reference_number = $invoice->key;
        $model->reference_emission_date = date('Y-m-d H:i:s');
        $model->reference_code = '01';
        $model->reference_reason = 'Anula documento de referencia';
        $model->consecutive = $model->generateConsecutive();

        //BEGIN payment method has invoice
        $payment_methods_assigned = PaymentMethodHasInvoice::getPaymentMethodByInvoiceId($invoice_id);

        $payment_methods_assigned_ids = [];
        foreach ($payment_methods_assigned as $value) {
            $payment_methods_assigned_ids[] = $value['payment_method_id'];
        }

        $model->payment_methods = $payment_methods_assigned_ids;
        //END payment method has invoice

        if ($model->load(Yii::$app->request->post())) {
            $model->emission_date = date('Y-m-d H:i:s');
            $model->reference_emission_date = date('Y-m-d H:i:s');
            $transaction = \Yii::$app->db->beginTransaction();

            try {
                $errors = 0;
                if (empty($model->reference_emission_date)) {
                    $model->addError('reference_emission_date', 'Debe especificar la fecha de emisión');
                    $errors++;
                }

                if (empty($model->reference_reason)) {
                    $model->addError('reference_reason', 'Debe especificar la razón por la cual se modifica la factura de referencia');
                    $errors++;
                }

                if ($errors > 0) {
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error actualizando el elemento'));

                    return $this->render('create', [
                        'model' => $model
                    ]);
                }

                if (CreditNote::find()->select(['consecutive'])->where(['consecutive' => $model->consecutive])->exists()) {
                    $model->consecutive = $model->generateConsecutive();
                }

                if ($model->reference_code == '01') {
                    $model->credit_note_type = UtilsConstants::CREDIT_NOTE_TYPE_TOTAL;
                    $invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE;
                } else {
                    $model->credit_note_type = UtilsConstants::CREDIT_NOTE_TYPE_PARTIAL;
                    $invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE_PARTIAL;
                }

                if ($model->save()) {
                    PaymentMethodHasCreditNote::updateRelation($model, [], 'payment_methods', 'payment_method_id');

                    //clonar los items de la factura y asociarlos a la nota de crédito
                    $items_associates = ItemInvoice::findAll(['invoice_id' => $invoice_id]);

                    foreach ($items_associates as $index => $item) {
                        $new_item = new ItemCreditNote();
                        $new_item->attributes = $item->attributes;
                        $new_item->credit_note_id = $model->id;
                        $new_item->tax_rate_percent = $item->tax_rate_percent;
                        $new_item->save();
                    }

                    $invoice->save(false);

                    $transaction->commit();

                    GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Elemento actualizado correctamente'));

                    return $this->redirect(['update', 'id' => $model->id]);
                } else {
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error actualizando el elemento'));
                }
            } catch (\Exception $e) {
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción actualizando el elemento'));
                $transaction->rollBack();
            }
        }

        return $this->render('create', [
            'model' => $model
        ]);
    }

    /**
     * Updates an existing CreditNote model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $status_hacienda = (int) $model->status_hacienda;
        $old_ready_to_send = (int) $model->ready_to_send_email;
        $old_email_sent = (int) $model->email_sent;

        if ($status_hacienda !== UtilsConstants::HACIENDA_STATUS_NOT_SENT) {
            GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'No es posible actualizar una nota de crédito enviada a hacienda'));
            return $this->redirect(['index']);
        }

        if (isset($model) && !empty($model)) {
            $searchModelItems = new ItemCreditNoteSearch();
            $searchModelItems->credit_note_id = $model->id;
            $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);
            if (is_null($model->status_hacienda)) {
                $model->status_hacienda = UtilsConstants::HACIENDA_STATUS_NOT_SENT;
            }

            //BEGIN payment method has credit_note
            $payment_methods_assigned = PaymentMethodHasCreditNote::getPaymentMethodByCreditNoteId($id);

            $payment_methods_assigned_ids = [];
            foreach ($payment_methods_assigned as $value) {
                $payment_methods_assigned_ids[] = $value['payment_method_id'];
            }

            $model->payment_methods = $payment_methods_assigned_ids;
            //END payment method has credit_note

            $old_status = (int)$model->status;

            if ($model->load(Yii::$app->request->post())) {
                $transaction = \Yii::$app->db->beginTransaction();
                $total_items = ItemCreditNote::find()->where(['credit_note_id' => $id])->count();
                $ready_to_send = (int) $model->ready_to_send_email;

                if ($ready_to_send === 1 && $total_items === 0) {
                    $model->ready_to_send_email = 0;
                    $model->addError('ready_to_send_email', 'No es posible marcar como "Lista para enviar" una nota de crédito sin items');
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'No es posible marcar como "Lista para enviar" una nota de crédito sin items'));

                    return $this->render('update', [
                        'model' => $model,
                        'searchModelItems' => $searchModelItems,
                        'dataProviderItems' => $dataProviderItems,
                    ]);
                }

                try {
                    PaymentMethodHasCreditNote::updateRelation($model, $payment_methods_assigned, 'payment_methods', 'payment_method_id');

                    if ($model->save()) {
                        $new_status = (int) $model->status;
                        if ($old_status !== $new_status) {
                            $model->verifyStock();
                        }

                        //Enviar factura por correo si aplica
                        if ($old_email_sent === 0 && $old_ready_to_send === 0 && $old_ready_to_send !== $ready_to_send) {
                            $email_model = new EnviarEmailForm();
                            $issuer = Issuer::find()->one();
                            $email_model->id = $model->id;
                            $email_model->de = $issuer->email;
                            $email_model->para = $model->customer->email;
                            $email_model->nombrearchivo = $model->key . '.pdf';
                            $email_model->asunto = 'Envío de Nota de Crédito Electrónica';
                            $response = $this->enviareamil($email_model, $model);

                            $model->email_sent = 1;
                            $model->save();
                        }

                        $transaction->commit();

                        GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Elemento actualizado correctamente'));

                        return $this->redirect(['index']);
                    } else {
                        GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error actualizando el elemento'));
                    }
                } catch (Exception $e) {
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción actualizando el elemento'));
                    $transaction->rollBack();
                }
            }
        } else {
            GlobalFunctions::addFlashMessage('warning', Yii::t('backend', 'El elemento buscado no existe'));
        }

        return $this->render('update', [
            'model' => $model,
            'searchModelItems' => $searchModelItems,
            'dataProviderItems' => $dataProviderItems,
        ]);
    }

    /**
     * Deletes an existing CreditNote model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $status_hacienda = (int) $model->status_hacienda;

        if ($status_hacienda === UtilsConstants::HACIENDA_STATUS_NOT_SENT) {
            $transaction = \Yii::$app->db->beginTransaction();

            try {
                $all_items = ItemCreditNote::find()->where(['credit_note_id' => $id])->all();
                foreach ($all_items as $key => $item) {
                }

                if ($model->delete()) {
                    $transaction->commit();

                    GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Elemento eliminado correctamente'));
                } else {
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error eliminando el elemento'));
                }
            } catch (Exception $e) {
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción eliminando el elemento'));
                $transaction->rollBack();
            }
        } else {
            GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'No se puede eliminar una factura enviada a Hacienda'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the CreditNote model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CreditNote the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CreditNote::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend', 'La página solicitada no existe'));
        }
    }

    public function actionGetResumeCreditNote($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = CreditNote::getResumeCreditNote($id);

        $total_price = $model->subtotal + $model->tax_amount;

        return \Yii::$app->response->data = [
            'total_subtotal' => GlobalFunctions::formatNumber($model->subtotal, 2),
            'total_tax' => GlobalFunctions::formatNumber($model->tax_amount, 2),
            'total_discount' => GlobalFunctions::formatNumber($model->discount_amount, 2),
            'total_exonerate' => GlobalFunctions::formatNumber($model->exonerate_amount, 2),
            'total_price' => GlobalFunctions::formatNumber($total_price, 2),
        ];
    }

    public function showPdf($ids, $original, $moneda = 'COLONES', $destino = 'browser', $filename = 'NotaCredito')
    {
        $logo = "<img src=\"" . Setting::getUrlLogoBySettingAndType(1, Setting::SETTING_ID) . "\" width=\"165\"/>";

        if (!is_array($ids) && !empty($ids))
            $ids = explode(',', $ids);

        $credit_notes = CreditNote::find()->where(['id' => $ids])->all();
        $data = '';
        foreach ($credit_notes as $credit_note) {
            $qr_code_credit_note = $credit_note->generateQrCode();
            $img_qr = '<img src="' . $qr_code_credit_note . '" width="100"/>';

            $items_credit_note = ItemCreditNote::find()->where(['credit_note_id' => $credit_note->id])->all();

            if (!empty($data))
                $data .= '<pagebreak>';

            $data .= $this->renderPartial('_pdf', [
                'credit_note' => $credit_note,
                'items_credit_note' => $items_credit_note,
                'logo' => $logo,
                'moneda' => $moneda,
                'original' => $original,
                'img_qr' => $img_qr
            ]);
        }

        $html_header =  $this->renderPartial('_pdf_header', [
            'credit_note' => $credit_note,
            'logo' => $logo,
            'moneda' => $moneda,
            'original' => $original,
        ]);

        if ($destino == 'browser') {
            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'destination' => Pdf::DEST_BROWSER,
                'content' => $data,
                'filename' => $filename,
                'options' => [
                    // any mpdf options you wish to set
                    'title' => 'NotaCredito',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => false,
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend', 'NotaCredito'),
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    'SetFooter' => ['|' . Yii::t('backend', 'Página') . ' {PAGENO}|'],
                ],
            ]);

            $pdf->marginTop = 100;
            $mpdf = $pdf->getApi();
            $mpdf->SetHTMLHeader($html_header);

            return $pdf->render();
        } else {
            if (!file_exists("uploads/credit_note/") || !is_dir("uploads/credit_note/")) {
                try {
                    FileHelper::createDirectory("uploads/credit_note/", 0777);
                } catch (\Exception $exception) {
                    Yii::info("Error handling NotaCredito folder resources");
                }
            }

            $file_pdf_save = Yii::getAlias('@backend') . '/web/uploads/credit_note/' . $filename;

            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'destination' => Pdf::DEST_FILE,
                'content' => $data,
                'filename' => $file_pdf_save,
                'options' => [
                    // any mpdf options you wish to set
                    'title' => 'NotaCredito',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => false,
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend', 'NotaCredito'),
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    'SetFooter' => ['|' . Yii::t('backend', 'Página') . ' {PAGENO}|'],
                ],
            ]);

            $pdf->marginTop = 100;
            $mpdf = $pdf->getApi();
            $mpdf->SetHTMLHeader($html_header);

            $pdf->render();

            return $file_pdf_save;
        }
    }

    public function actionViewpdf($id)
    {
        $credit_note = $this->findModel($id);

        return $this->showPdf($id, true);
    }

    public function actionViewxml($id)
    {
        $credit_note = CreditNote::find()->where(['id' => $id])->one();
        $items_credit_note = ItemCreditNote::find()->where(['credit_note_id' => $id])->all();

        $apiXML = new ApiXML();
        $issuer = Issuer::find()->one();
        $xml = $apiXML->genXMLNC($issuer, $credit_note, $items_credit_note);

        $p12Url = $issuer->getFilePath();
        $pinP12 = $issuer->certificate_pin;

        $doc_type = '03'; // Nota de crédito
        $apiFirma = new ApiFirmadoHacienda();
        $xmlFirmado = $apiFirma->firmar($p12Url, $pinP12, $xml, $doc_type);

        $xml = base64_decode($xmlFirmado);

        // http response
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_RAW;
        $headers = Yii::$app->response->headers;
        $headers->add('Content-Type', 'text/xml');
        $headers->add('Content-Disposition', 'attachment; filename=' . 'NC-' . $credit_note->key . ".xml");
        return $xml;
    }

    public function actionEnviarFacturaEmail($id)
    {
        $model = new EnviarEmailForm();
        $model->id = $id;
        $msg = '';
        if ($model->load(Yii::$app->request->post())) {
            $model->id = Yii::$app->request->post()['EnviarEmailForm']['id'];

            $factura = CreditNote::findOne($id);
            $model->nombrearchivo = '';
            $model->nombrearchivo .= $factura->key . '.pdf';
            $model->cc = Yii::$app->request->post()['EnviarEmailForm']['cc'];
            $model->cuerpo = Yii::$app->request->post()['EnviarEmailForm']['cuerpo'];

            $respuesta = $this->enviareamil($model, $factura);

            Yii::$app->response->format = 'json';
            if ($respuesta) {
                $factura->ready_to_send_email = 1;
                $factura->email_sent = 1;
                $factura->save(false);

                $msg .= 'Se ha enviado la nota de crédito por correo electrónico';
                $type = 'success';
            } else {
                $msg .= 'Ha ocurrido un error. No se ha podido enviar el correo electrónico';
                $type = 'danger';
            }

            return \Yii::$app->response->data  = [
                'message' => $msg,
                'type' => $type,
                'titulo' => "Informaci&oacute;n <hr class=\"kv-alert-separator\">",
            ];
        } else {
            $credit_note = CreditNote::findOne($id);
            $emisor = Issuer::find()->one();
            $model->de = $emisor->email;
            $model->para = $credit_note->customer->email;

            $model->nombrearchivo = $credit_note->key . '.pdf';
            $model->asunto = 'Envío de Nota de Crédito Electrónica';
            $model->cc = '';

            return $this->renderAjax('_emailForm', [
                'model' => $model,
            ]);
        }
    }

    public function enviareamil($model, $factura)
    {
        $user = yii::$app->user->identity;
        $respuesta = false;
        $emisor = Issuer::find()->one();

        if (strlen(trim($model->cc)) > 0) {
            $arr_cc = explode(';', $model->cc);
        } else {
            $arr_cc = array();
        }

        $direcciones_ok = true;
        foreach ($arr_cc as $ccs) {
            if (!filter_var($ccs, FILTER_VALIDATE_EMAIL)) {
                $direcciones_ok = false;
                break;
            }
        }
        if ($direcciones_ok == false) {
            $messageType = 'danger';
            $message = "<strong>Error!</strong> El correo no se pudo enviar, revise las direcciones de los destinatarios de copia ";
            return false;
        }
        if ($direcciones_ok == true) {
            if (!filter_var($model->de, FILTER_VALIDATE_EMAIL)) {
                $direcciones_ok = false;
                $messageType = 'danger';
                $message = "<strong>Error!</strong> El correo no se pudo enviar, revise la dirección del remitente ";
                return false;
            }
        }
        if ($direcciones_ok == true) {
            $to = explode(';', $model->para);

            if (empty($model->from)) {
                $from = $user->email;
            }

            $archivo = NULL;

            $body_html = "<table width=\"70%\" align=\"center\" style=\"border-collapse:collapse;\">
				<tr>
					<td align=\"center\">
						<br />        
						<span style=\"text-align:center;color:#3157F2;font-size:20px;\">Envío de Nota de Crédito Electrónica</span><br />
						<span style=\"text-align:center;font-size:18px; color:#666\">Le adjuntamos los DOCUMENTOS procesados</span>
						<br />    
						<br />                
					</td>    
				</tr>
				<tr>
					<td align=\"center\">
						<table width=\"50%\" align=\"center\" border=\"1\" cellpadding=\"10px\" cellspacing=\"0\">
							<thead>
								<tr>
									<td colspan=\"2\">
										<strong>Clave Numérica: " . $factura->key . "</strong>
									</td>
								</tr>
								<tr>
									<th style=\"background-color:#CCC; text-align:left\">Emisor</th>
									<td>" . $emisor->name . "</td>
								</tr>
								<tr>
									<th style=\"background-color:#CCC; text-align:left\">Receptor</th>
									<td>" . $factura->customer->name . "</td>
								</tr>     
								<tr>
									<th style=\"background-color:#CCC; text-align:left\">No. Documento</th>
									<td>" . $factura->consecutive . "</td>
								</tr>                          
								<tr>
									<th style=\"background-color:#CCC; text-align:left\">Fecha</th>
									<td>" . date('d-m-Y', strtotime($factura->emission_date)) . "</td>
								</tr>                          
								<tr>
									<th style=\"background-color:#CCC; text-align:left\">Importe</th>
									<td>" . $factura->currency->symbol . " " . number_format($factura->getTotalAmount(), 2, ".", ",") . "</td>
								</tr>                          
							</thead>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						" . $model->cuerpo . "
					</td>
				</tr>
				<tr>
					<td>
						<br />
						<p style=\"text-align:center\">
							Este correo electrónico y cualquier anexo al mismo, contiene información de caracter confidencial
							exclusivamente dirigida a su destinatario o destinatarios. En el caso de haber recibido este correo electrónico
							por error, se ruega la destrucción del mismo.
						</p>
						<p style=\"text-align:center\">
							Copyright © 2021 facturaelectronicacrc.com Powered By <a href=\"https://www.softwaresolutions.co.cr\">softwaresolutions S.A</a><br />
							Todos los derechos reservados
						</p>            
					</td>
				</tr>
			</table>";


            $mensage = Yii::$app->mail->compose("layouts/html", ['content' => $model->cuerpo])
                ->setTo($to)
                ->setFrom($model->de)
                ->setCc($arr_cc)
                ->setSubject($model->asunto)
                ->setTextBody($body_html)
                ->setHtmlBody($body_html);

            // Adjuntar PDF
            $nombrearchivo = 'NC-' . $factura->key . '.pdf';
            $archivo = '';
            $listid[] = $factura->id;
            $archivo = $this->showPdf($listid, true, 'COLONES', 'file', $nombrearchivo);
            if (!empty($archivo)) {
                $mensage->attach($archivo, ['fileName' => $nombrearchivo]);
            }


            // Adjuntar XML del SISTEMA
            $invoice = CreditNote::find()->where(['id' => $factura->id])->one();
            $items_invoice = ItemCreditNote::find()->where(['credit_note_id' => $factura->id])->all();

            $apiXML = new ApiXML();
            $issuer = Issuer::find()->one();
            $xml = $apiXML->genXMLNC($issuer, $invoice, $items_invoice);

            $p12Url = $issuer->getFilePath();
            $pinP12 = $issuer->certificate_pin;

            $doc_type = '03';
            $apiFirma = new ApiFirmadoHacienda();
            $xmlFirmado = $apiFirma->firmar($p12Url, $pinP12, $xml, $doc_type);

            $xml = base64_decode($xmlFirmado);

            $nombre_archivo = 'NC-' . $factura->key . '.xml';
            // create attachment on-the-fly
            $mensage->attachContent($xml, ['fileName' => $nombre_archivo, 'contentType' => 'text/plain']);


            // Adjuntar XML de respuesta de Hacienda si existe
            $url_xml_hacienda_verificar = Yii::getAlias('@backend/web/uploads/xmlh/NC-MH-' . $factura->key . '.xml');
            $nombre_archivo = 'NC-MH' . $factura->key . '.xml';
            if (file_exists($url_xml_hacienda_verificar))
                $mensage->attach($url_xml_hacienda_verificar, ['fileName' => $nombre_archivo]);


            if ($mensage->send())
                $respuesta = true;
            else
                $respuesta = false;
        }
        return $respuesta;
    }

    /**
     * @param $id
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionEnviarFacturaHacienda($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $credit_note = CreditNote::findOne($id);

        $datos = $this->validaDatosFactura($credit_note);
        $emisor = Issuer::find()->one();
        $error = $datos['error'];
        if ($error == 0) {
            // Si todas las validaciones son correctas, proceder al proceso
            // Logearse en la api y obtener el token;
            $apiAccess = new ApiAccess();
            $datos = $apiAccess->loginHacienda($emisor);

            $error = $datos['error'];
            if ($error == 0) {
                // Obtener el xml firmado electrónicamente
                $credit_note = CreditNote::find()->where(['id' => $id])->one();
                $items_credit_note = ItemCreditNote::find()->where(['credit_note_id' => $id])->all();

                $apiXML = new ApiXML();
                $issuer = Issuer::find()->one();
                $xml = $apiXML->genXMLNC($issuer, $credit_note, $items_credit_note);

                $p12Url = $issuer->getFilePath();
                $pinP12 = $issuer->certificate_pin;

                $doc_type = '03'; // NotaCredito
                $apiFirma = new ApiFirmadoHacienda();
                $xmlFirmado = $apiFirma->firmar($p12Url, $pinP12, $xml, $doc_type);

                $xml = base64_decode($xmlFirmado);

                // Enviar documento a hacienda
                $apiEnvioHacienda = new ApiEnvioHacienda();
                $datos = $apiEnvioHacienda->send($xmlFirmado, $apiAccess->token, $credit_note, $emisor, $doc_type);
                // En $datos queda el mensaje de respuesta

                $respuesta = $datos['response'];
                $code = $respuesta->getHeaders()->get('http-code');
                if ($code == '202' || $code == '201' || $code == '200') {
                    $mensaje = "La nota de crédito electrónica con clave: [" . $credit_note->key . "] se recibió correctamente, queda pendiente la validación de esta y el envío de la respuesta de parte de Hacienda.";
                    $credit_note->status_hacienda = UtilsConstants::HACIENDA_STATUS_RECEIVED; // Recibido
                    $credit_note->save(false);
                    $type = 'success';
                    $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
                } elseif ($code == '400') {
                    $error = 1;
                    $mensaje = utf8_encode($respuesta->getHeaders()->get('X-Error-Cause'));
                    $type = 'danger';
                    $titulo = "Error <hr class=\"kv-alert-separator\">";
                } else {
                    $error = 1;
                    $mensaje = "Ha ocurrido un error desconocido al enviar la factura electrónica con clave: [" . $credit_note->key . "]. Póngase en contacto con el administrador del sistema";
                    $type = 'danger';
                    $titulo = "Error <hr class=\"kv-alert-separator\">";
                }
            } else {
                $error = 1;
                $mensaje = $datos['mensaje'];
                $type = 'danger';
                $titulo = "Error <hr class=\"kv-alert-separator\">";
            }

            $apiAccess->CloseSesion($apiAccess->token, $emisor);
        } else {
            $mensaje = $datos['mensaje'];
            $type = $datos['type'];
            $titulo = $datos['titulo'];
        }

        return ['mensaje' => $mensaje, 'type' => $type, 'titulo' => $titulo];
    }

    /**
     * @param $credit_note
     * @return array
     */
    public function validaDatosFactura($credit_note)
    {
        // Valida que los datos de la nota de crédito, que tenga detalle y emisor definido
        $error = 0;
        $mensaje = '';
        $type = '';
        $titulo    = '';
        if (is_null($credit_note)) {
            $error = 1;
            $mensaje = 'La nota de crédito seleccionada no se encuentra en la base de datos';
            $type = 'danger';
            $titulo = "Error <hr class=\"kv-alert-separator\">";
        }

        $items_exists = ItemCreditNote::find()->where(['credit_note_id' => $credit_note->id])->exists();
        if (!$items_exists) {
            $error = 1;
            $mensaje = 'La nota de crédito seleccionada no contiene ninguna línea de producto / servicio. Por favor revise la información e inténtelo nuevamente';
            $type = 'warning';
            $titulo = "Advertencia <hr class=\"kv-alert-separator\">";
        }


        $configuracion = Issuer::find()->one();
        if (is_null($configuracion)) {
            $error = 1;
            $mensaje = 'No se ha podido obtener la información del emisor de la nota de crédito. Por favor revise los datos e inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema';
            $type = 'danger';
            $titulo = "Error <hr class=\"kv-alert-separator\">";
        }

        return ['error' => $error, 'mensaje' => $mensaje, 'type' => $type, 'titulo' => $titulo];
    }

    /**
     * @param $id
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetEstadoFacturaHacienda($id)
    {
        $credit_note = CreditNote::findOne($id);
        $emisor = Issuer::find()->one();
        $this->getEstadoFactura($credit_note, $emisor);
    }

    /**
     * @param CreditNote $credit_note
     * @param Issuer $emisor
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getEstadoFactura($credit_note, $emisor)
    {
        $credit_note_id = $credit_note->id;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $datos = $this->validaDatosFactura($credit_note);
        $error = $datos['error'];
        $actualizar = 0;
        $proceder = true;

        $status_hacienda = (int) $credit_note->status_hacienda;
        if ($status_hacienda === UtilsConstants::HACIENDA_STATUS_NOT_SENT || $status_hacienda === UtilsConstants::HACIENDA_STATUS_RECEIVED) {
            if ($error == 0 && $proceder == true) {
                // Si todas las validaciones son correctas, proceder al proceso
                // Logearse en la api y obtener el token;
                $apiAccess = new ApiAccess();
                $datos = $apiAccess->loginHacienda($emisor);
                $error = $datos['error'];
                if ($error == 0) {
                    // consultar estado de documento en hacienda
                    $apiConsultaHacienda = new ApiConsultaHacienda();
                    $tipoDocumento = '03'; // NotaCredito
                    $datos = $apiConsultaHacienda->getEstado($credit_note, $emisor, $apiAccess->token, $tipoDocumento);
                    // En $datos queda el mensaje de respuesta
                    $apiAccess->CloseSesion($apiAccess->token, $emisor);
                    $actualizar = $datos['actualizar'];
                    $mensaje = $datos['mensaje'];
                    $type = $datos['type'];
                    $titulo = $datos['titulo'];
                    $estado = $datos['estado'];

                    if ($estado == 'aceptado') {
                        $nota = CreditNote::find()->where(['id'=>$credit_note_id])->one();
                        CreditNote::setStatusHacienda($credit_note_id, UtilsConstants::HACIENDA_STATUS_ACCEPTED); // Aceptada

                        // Verifico si el estado ya estaba en aceptada entonces no enviar email ni rebajar de inventario
                        //if ($nota->status_hacienda != UtilsConstants::HACIENDA_STATUS_ACCEPTED)
                        if ($nota->status_hacienda == UtilsConstants::HACIENDA_STATUS_RECEIVED)  
                        {          
                            //devolver todos los items a inventario
                            $all_items = ItemCreditNote::find()->where(['credit_note_id' => $credit_note_id])->all();
                            foreach ($all_items as $key => $model) {
                                $observations = 'Devolución por aceptación en Hacienda de la Nota de crédito #' . $model->creditNote->key;
                                $adjustment_type = UtilsConstants::ADJUSTMENT_TYPE_CREDIT_NOTE;

                                // Chequear si ya se ha realizado ese ajuste
                                $adjustment = Adjustment::find()->where(['product_id'=>$model->product_id, 'key'=> $nota->key, 'type'=>$adjustment_type, 
                                                'origin_branch_office_id'=>$nota->branch_office_id, 'observations'=>$observations])->one();
                                if (is_null($adjustment)){
                                    if (isset($model->product_id) && !empty($model->product_id)) {                                
                                        // Se calcula la cantidad a extraer según la unidad de medidad del producto
                                        $quantity = Product::getUnitQuantityByItem($model->product_id, $model->quantity, $model->unit_type_id);                            
                                        Product::returnToInventory($model->product_id, $adjustment_type, $model->creditNote->branch_office_id, $quantity, $model->credit_note_id, false, $observations, $nota->key);
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $mensaje = 'No se ha podido autenticar en la api de hacienda. Inténtelo nuevamente';
                    $type = 'danger';
                    $titulo = "Error <hr class=\"kv-alert-separator\">";
                }
            }
        } else {
            $mensaje = 'El estado de hacienda ya fue recibido';
            $type = 'warning';
            $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
            $actualizar = 0;
        }

        return \Yii::$app->response->data  =  ['mensaje' => $mensaje, 'type' => $type, 'titulo' => $titulo, 'actualizar' => $actualizar];
    }


    public function printPdf($ids)
    {
        $logo = "<img src=\"" . Setting::getUrlLogoBySettingAndType(1, Setting::SETTING_ID) . "\" width=\"100\"/>";

        if (!is_array($ids) && !empty($ids))
            $ids = explode(',', $ids);

        $credit_notes = CreditNote::find()->where(['id' => $ids])->all();
        $data = '';
        foreach ($credit_notes as $credit_note) {
            $qr_code_credit_note = $credit_note->generateQrCode();
            $img_qr = '<img src="' . $qr_code_credit_note . '" width="50"/>';

            $items_credit_note = ItemCreditNote::find()->where(['credit_note_id' => $credit_note->id])->all();

            if (!empty($data))
                $data .= '<pagebreak>';

            $data .= $this->renderPartial('_print_pdf', [
                'credit_note' => $credit_note,
                'items_credit_note' => $items_credit_note,
                'logo' => $logo,
                'moneda' => "COLONES",
                'original' => true,
                'img_qr' => $img_qr
            ]);

            $data .= '<pagebreak>';

            $data .= $this->renderPartial('_print_pdf', [
                'credit_note' => $credit_note,
                'items_credit_note' => $items_credit_note,
                'logo' => $logo,
                'moneda' => "COLONES",
                'original' => false,
                'img_qr' => $img_qr
            ]);
        }

        $html_header =  $this->renderPartial('_print_pdf_header', [
            'credit_note' => $credit_note,
            'logo' => $logo,
            'moneda' => "COLONES",
            'original' => false,
        ]);        


        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE,
            'orientation' => Pdf::ORIENT_LANDSCAPE,
            'destination' => Pdf::DEST_BROWSER,
            'content' => $data,
            'filename' => "NotaCredito.pdf",
            'options' => [
                // any mpdf options you wish to set
                'title' => 'NotaCredito',
                'defaultheaderline' => 0,
                //'default_font' => 'Calibri',
                'setAutoTopMargin' => 'stretch',
                'showWatermarkText' => false,
            ],
            'methods' => [
                'SetTitle' => Yii::t('backend', 'NotaCredito'),
                'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                //'SetFooter' => ['|'.Yii::t('backend','Página').' {PAGENO}|'],
            ],
        ]);

        $pdf->marginTop = 60;
        $mpdf = $pdf->getApi();
        $mpdf->SetHTMLHeader($html_header);

        //$pdf->marginTop = 5;
        $pdf->marginBottom = 0;
        $pdf->marginHeader = 0;
        $pdf->marginFooter = 0;
        $pdf->defaultFontSize = 12;

        return $pdf->render();
    }

    public function actionPrintpdf($id)
    {
        return $this->printPdf($id);
    }
}
