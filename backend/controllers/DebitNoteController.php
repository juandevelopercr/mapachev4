<?php

namespace backend\controllers;

use backend\models\business\Invoice;
use backend\models\business\ItemDebitNote;
use backend\models\business\ItemDebitNoteSearch;
use backend\models\business\ItemInvoice;
use backend\models\business\PaymentMethodHasDebitNote;
use backend\models\business\PaymentMethodHasInvoice;
use backend\models\business\SellerHasDebitNote;
use backend\models\business\CollectorHasDebitNote;
use backend\models\business\SellerHasInvoice;
use backend\models\business\CollectorHasInvoice;
use backend\models\business\Product;
use backend\models\nomenclators\UtilsConstants;
use backend\models\settings\Issuer;
use backend\models\settings\Setting;
use common\components\ApiV43\ApiAccess;
use common\components\ApiV43\ApiConsultaHacienda;
use common\components\ApiV43\ApiEnvioHacienda;
use common\components\ApiV43\ApiFirmadoHacienda;
use common\components\ApiV43\ApiXML;
use common\models\EnviarEmailForm;
use kartik\mpdf\Pdf;
use Yii;
use backend\models\business\DebitNote;
use backend\models\business\DebitNoteSearch;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\db\Exception;

/**
 * DebitNoteController implements the CRUD actions for DebitNote model.
 */
class DebitNoteController extends Controller
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
     * Lists all DebitNote models.
     * @return mixed
     */
    public function actionIndex()
    {
        $is_point_sale = 0;
        $searchModel = new DebitNoteSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $is_point_sale);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single DebitNote model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModelItems = new ItemDebitNoteSearch(['debit_note_id' => $id]);
        $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);

        return $this->render('view', [
            'model' => $model,
            'dataProviderItems' => $dataProviderItems,
        ]);
    }

    public function actionCreate($invoice_id)
    {
        $model = new DebitNote();
        $model->LoadDefaultValues();

        $invoice = Invoice::findOne($invoice_id);
        if (count($invoice->itemInvoices) <= 0)
            GlobalFunctions::addFlashMessage('danger', 'La factura electrónica no tiene ningún item asociado');

        $model->attributes = $invoice->attributes;

        $model->debit_note_type = UtilsConstants::DEBIT_NOTE_TYPE_TOTAL;
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

        //BEGIN seller has seller
        $seller_assigned = SellerHasInvoice::getSellerByInvoiceId($invoice_id);

        $seller_assigned_ids = [];
        foreach ($seller_assigned as $value) {
            $seller_assigned_ids[] = $value['seller_id'];
        }

        $model->sellers = $seller_assigned_ids;
        //END seller method has seller


        //BEGIN collector has collector
        $collector_assigned = CollectorHasInvoice::getCollectorByInvoiceId($invoice_id);

        $collector_assigned_ids = [];
        foreach ($collector_assigned as $value) {
            $collector_assigned_ids[] = $value['collector_id'];
        }

        $model->collectors = $collector_assigned_ids;
        //END seller method has collector        

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

                if (DebitNote::find()->select(['consecutive'])->where(['consecutive' => $model->consecutive])->exists()) {
                    $model->consecutive = $model->generateConsecutive();
                }

                if ($model->reference_code == '01') {
                    $model->debit_note_type = UtilsConstants::DEBIT_NOTE_TYPE_TOTAL;
                    $invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_DEBIT_NOTE;
                } else {
                    $model->debit_note_type = UtilsConstants::DEBIT_NOTE_TYPE_PARTIAL;
                    $invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_DEBIT_NOTE_PARTIAL;
                }

                if ($model->save()) {
                    PaymentMethodHasDebitNote::updateRelation($model, [], 'payment_methods', 'payment_method_id');

                    //SellerHasDebitNote::updateRelation($model, [], 'sellers', 'seller_id');

                    //CollectorHasDebitNote::updateRelation($model, [], 'collectors', 'collector_id');

                    //clonar los items de la factura y asociarlos a la nota de débito
                    $items_associates = ItemInvoice::findAll(['invoice_id' => $invoice_id]);

                    foreach ($items_associates as $index => $item) {
                        $new_item = new ItemDebitNote();
                        $new_item->attributes = $item->attributes;
                        $new_item->debit_note_id = $model->id;
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
     * Updates an existing DebitNote model.
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
        $model->reference_emission_date = date('Y-m-d H:i:s', strtotime($model->reference_emission_date));

        if ($status_hacienda !== UtilsConstants::HACIENDA_STATUS_NOT_SENT) {
            GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'No es posible actualizar una nota de débito enviada a hacienda'));
            return $this->redirect(['index']);
        }

        if (isset($model) && !empty($model)) {
            $searchModelItems = new ItemDebitNoteSearch();
            $searchModelItems->debit_note_id = $model->id;
            $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);
            if (is_null($model->status_hacienda)) {
                $model->status_hacienda = UtilsConstants::HACIENDA_STATUS_NOT_SENT;
            }

            //BEGIN payment method has debit_note
            $payment_methods_assigned = PaymentMethodHasDebitNote::getPaymentMethodByDebitNoteId($id);

            $payment_methods_assigned_ids = [];
            foreach ($payment_methods_assigned as $value) {
                $payment_methods_assigned_ids[] = $value['payment_method_id'];
            }

            $model->payment_methods = $payment_methods_assigned_ids;
            //END payment method has debit_note

            /*
            //BEGIN seller has debit_note
            $seller_assigned = SellerHasDebitNote::getSellerByDebitNoteId($id);

            $seller_assigned_ids = [];
            foreach ($seller_assigned as $value) {
                $seller_assigned_ids[] = $value['seller_id'];
            }

            $model->sellers = $seller_assigned_ids;
            //END seller method has debit_note


            //BEGIN collector has debit_note
            $collector_assigned = CollectorHasDebitNote::getCollectorByDebitNoteId($id);

            $collector_assigned_ids = [];
            foreach ($collector_assigned as $value) {
                $collector_assigned_ids[] = $value['collector_id'];
            }

            $model->collectors = $collector_assigned_ids;
            //END seller method has debit_note  
            */          

            $old_status = (int)$model->status;

            if ($model->load(Yii::$app->request->post())) {
                $transaction = \Yii::$app->db->beginTransaction();
                $total_items = ItemDebitNote::find()->where(['debit_note_id' => $id])->count();
                $ready_to_send = (int) $model->ready_to_send_email;

                if ($ready_to_send === 1 && $total_items === 0) {
                    $model->ready_to_send_email = 0;
                    $model->addError('ready_to_send_email', 'No es posible marcar como "Lista para enviar" una nota de débito sin items');
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'No es posible marcar como "Lista para enviar" una nota de débito sin items'));

                    return $this->render('update', [
                        'model' => $model,
                        'searchModelItems' => $searchModelItems,
                        'dataProviderItems' => $dataProviderItems,
                    ]);
                }

                try {
                    PaymentMethodHasDebitNote::updateRelation($model, $payment_methods_assigned, 'payment_methods', 'payment_method_id');

                    //SellerHasDebitNote::updateRelation($model, $seller_assigned, 'sellers', 'seller_id');

                    //CollectorHasDebitNote::updateRelation($model, $collector_assigned, 'collectors', 'collector_id');

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
                            $email_model->asunto = 'Envío de Nota de Débito Electrónica';
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
     * Deletes an existing DebitNote model.
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
                $all_items = ItemDebitNote::find()->where(['debit_note_id' => $id])->all();

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
     * Finds the DebitNote model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return DebitNote the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DebitNote::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend', 'La página solicitada no existe'));
        }
    }

    public function actionGetResumeDebitNote($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = DebitNote::getResumeDebitNote($id);

        $total_price = $model->subtotal + $model->tax_amount;

        return \Yii::$app->response->data = [
            'total_subtotal' => GlobalFunctions::formatNumber($model->subtotal, 2),
            'total_tax' => GlobalFunctions::formatNumber($model->tax_amount, 2),
            'total_discount' => GlobalFunctions::formatNumber($model->discount_amount, 2),
            'total_exonerate' => GlobalFunctions::formatNumber($model->exonerate_amount, 2),
            'total_price' => GlobalFunctions::formatNumber($total_price, 2),
        ];
    }

    public function showPdf($ids, $original, $moneda = 'COLONES', $destino = 'browser', $filename = 'NotaDebito')
    {
        $logo = "<img src=\"" . GlobalFunctions::BASE_URL. Setting::getUrlLogoBySettingAndType(1, Setting::SETTING_ID) . "\" width=\"165\"/>";

        if (!is_array($ids) && !empty($ids))
            $ids = explode(',', $ids);

        $debit_notes = DebitNote::find()->where(['id' => $ids])->all();
        $data = '';
        foreach ($debit_notes as $debit_note) {
            $qr_code_debit_note = $debit_note->generateQrCode();
            $img_qr = '<img src="' . $qr_code_debit_note . '" width="100"/>';

            $items_debit_note = ItemDebitNote::find()->where(['debit_note_id' => $debit_note->id])->all();

            if (!empty($data))
                $data .= '<pagebreak>';

            $data .= $this->renderPartial('_pdf', [
                'debit_note' => $debit_note,
                'items_debit_note' => $items_debit_note,
                'logo' => $logo,
                'moneda' => $moneda,
                'original' => $original,
                'img_qr' => $img_qr
            ]);
        }

        $html_header =  $this->renderPartial('_pdf_header', [
            'debit_note' => $debit_note,
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
                    'title' => 'NotaDebito',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => false,
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend', 'NotaDebito'),
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    'SetFooter' => ['|' . Yii::t('backend', 'Página') . ' {PAGENO}|'],
                ],
            ]);

            $pdf->marginTop = 100;
            $mpdf = $pdf->getApi();
            $mpdf->SetHTMLHeader($html_header);

            return $pdf->render();
        } else {
            if (!file_exists("uploads/debit_note/") || !is_dir("uploads/debit_note/")) {
                try {
                    FileHelper::createDirectory("uploads/debit_note/", 0777);
                } catch (\Exception $exception) {
                    Yii::info("Error handling NotaDebito folder resources");
                }
            }

            $file_pdf_save = Yii::getAlias('@backend') . '/web/uploads/debit_note/' . $filename;

            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'destination' => Pdf::DEST_FILE,
                'content' => $data,
                'filename' => $file_pdf_save,
                'options' => [
                    // any mpdf options you wish to set
                    'title' => 'NotaDebito',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => false,
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend', 'NotaDebito'),
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
        return $this->showPdf($id, true);
    }

    public function actionViewxml($id)
    {
        $debit_note = DebitNote::find()->where(['id' => $id])->one();
        $items_debit_note = ItemDebitNote::find()->where(['debit_note_id' => $id])->all();

        $apiXML = new ApiXML();
        $issuer = Issuer::find()->one();
        $xml = $apiXML->genXMLND($issuer, $debit_note, $items_debit_note);

        $p12Url = $issuer->getFilePath();
        $pinP12 = $issuer->certificate_pin;

        $doc_type = '02'; // Nota de débito
        $apiFirma = new ApiFirmadoHacienda();
        $xmlFirmado = $apiFirma->firmar($p12Url, $pinP12, $xml, $doc_type);

        $xml = base64_decode($xmlFirmado);

        // http response
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_RAW;
        $headers = Yii::$app->response->headers;
        $headers->add('Content-Type', 'text/xml');
        $headers->add('Content-Disposition', 'attachment; filename=' . 'ND-' . $debit_note->key . ".xml");
        return $xml;
    }

    public function actionEnviarFacturaEmail($id)
    {
        $model = new EnviarEmailForm();
        $model->id = $id;
        $msg = '';
        if ($model->load(Yii::$app->request->post())) {
            $model->id = Yii::$app->request->post()['EnviarEmailForm']['id'];

            $debit_note = DebitNote::findOne($id);
            $model->nombrearchivo = '';
            $model->nombrearchivo .= $debit_note->key . '.pdf';
            $model->cc = Yii::$app->request->post()['EnviarEmailForm']['cc'];
            $model->cuerpo = Yii::$app->request->post()['EnviarEmailForm']['cuerpo'];

            $respuesta = $this->enviareamil($model, $debit_note);

            Yii::$app->response->format = 'json';
            if ($respuesta) {
                $debit_note->ready_to_send_email = 1;
                $debit_note->email_sent = 1;
                $debit_note->save(false);

                $msg .= 'Se ha enviado la nota de débito por correo electrónico';
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
            $debit_note = DebitNote::findOne($id);
            $emisor = Issuer::find()->one();
            $model->de = $emisor->email;
            $model->para = $debit_note->customer->email;

            $model->nombrearchivo = $debit_note->key . '.pdf';
            $model->asunto = 'Envío de Nota de Débito Electrónica';
            $model->cc = '';

            return $this->renderAjax('_emailForm', [
                'model' => $model,
            ]);
        }
    }

    public function enviareamil($model, $debit_note)
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
						<span style=\"text-align:center;color:#3157F2;font-size:20px;\">Envío de Nota de Débito Electrónica</span><br />
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
										<strong>Clave Numérica: " . $debit_note->key . "</strong>
									</td>
								</tr>
								<tr>
									<th style=\"background-color:#CCC; text-align:left\">Emisor</th>
									<td>" . $emisor->name . "</td>
								</tr>
								<tr>
									<th style=\"background-color:#CCC; text-align:left\">Receptor</th>
									<td>" . $debit_note->customer->name . "</td>
								</tr>     
								<tr>
									<th style=\"background-color:#CCC; text-align:left\">No. Documento</th>
									<td>" . $debit_note->consecutive . "</td>
								</tr>                          
								<tr>
									<th style=\"background-color:#CCC; text-align:left\">Fecha</th>
									<td>" . date('d-m-Y', strtotime($debit_note->emission_date)) . "</td>
								</tr>                          
								<tr>
									<th style=\"background-color:#CCC; text-align:left\">Importe</th>
									<td>" . $debit_note->currency->symbol . " " . number_format($debit_note->getTotalAmount(), 2, ".", ",") . "</td>
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
            $nombrearchivo = 'ND-' . $debit_note->key . '.pdf';
            $archivo = '';
            $listid[] = $debit_note->id;
            $archivo = $this->showPdf($listid, true, 'COLONES', 'file', $nombrearchivo);
            if (!empty($archivo)) {
                $mensage->attach($archivo, ['fileName' => $nombrearchivo]);
            }


            // Adjuntar XML del SISTEMA
            $invoice = DebitNote::find()->where(['id' => $debit_note->id])->one();
            $items_invoice = ItemDebitNote::find()->where(['debit_note_id' => $debit_note->id])->all();

            $apiXML = new ApiXML();
            $issuer = Issuer::find()->one();
            $xml = $apiXML->genXMLND($issuer, $invoice, $items_invoice);

            $p12Url = $issuer->getFilePath();
            $pinP12 = $issuer->certificate_pin;

            $doc_type = '02';
            $apiFirma = new ApiFirmadoHacienda();
            $xmlFirmado = $apiFirma->firmar($p12Url, $pinP12, $xml, $doc_type);

            $xml = base64_decode($xmlFirmado);

            $nombre_archivo = 'ND-' . $debit_note->key . '.xml';
            // create attachment on-the-fly
            $mensage->attachContent($xml, ['fileName' => $nombre_archivo, 'contentType' => 'text/plain']);

            // Adjuntar XML de respuesta de Hacienda si existe
            $url_xml_hacienda_verificar = Yii::getAlias('@backend/web/uploads/xmlh/ND-MH-' . $debit_note->key . '.xml');
            $nombre_archivo = 'ND-MH' . $debit_note->key . '.xml';
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
        $debit_note = DebitNote::findOne($id);

        $datos = $this->validaDatosFactura($debit_note);
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
                $debit_note = DebitNote::find()->where(['id' => $id])->one();
                $items_debit_note = ItemDebitNote::find()->where(['debit_note_id' => $id])->all();

                $apiXML = new ApiXML();
                $issuer = Issuer::find()->one();
                $xml = $apiXML->genXMLND($issuer, $debit_note, $items_debit_note);

                $p12Url = $issuer->getFilePath();
                $pinP12 = $issuer->certificate_pin;

                $doc_type = '02'; // NotaDebito
                $apiFirma = new ApiFirmadoHacienda();
                $xmlFirmado = $apiFirma->firmar($p12Url, $pinP12, $xml, $doc_type);

                $xml = base64_decode($xmlFirmado);

                // Enviar documento a hacienda
                $apiEnvioHacienda = new ApiEnvioHacienda();
                $datos = $apiEnvioHacienda->send($xmlFirmado, $apiAccess->token, $debit_note, $emisor, $doc_type);
                // En $datos queda el mensaje de respuesta

                $respuesta = $datos['response'];
                $code = $respuesta->getHeaders()->get('http-code');
                if ($code == '202' || $code == '201' || $code == '200') {
                    $mensaje = "La nota de débito electrónica con clave: [" . $debit_note->key . "] se recibió correctamente, queda pendiente la validación de esta y el envío de la respuesta de parte de Hacienda.";
                    $debit_note->status_hacienda = UtilsConstants::HACIENDA_STATUS_RECEIVED; // Recibido
                    $debit_note->save(false);
                    $type = 'success';
                    $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
                } elseif ($code == '400') {
                    $error = 1;
                    $mensaje = utf8_encode($respuesta->getHeaders()->get('X-Error-Cause'));
                    $type = 'danger';
                    $titulo = "Error <hr class=\"kv-alert-separator\">";
                } else {
                    $error = 1;
                    $mensaje = "Ha ocurrido un error desconocido al enviar la factura electrónica con clave: [" . $debit_note->key . "]. Póngase en contacto con el administrador del sistema";
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
     * @param $debit_note
     * @return array
     */
    public function validaDatosFactura($debit_note)
    {
        // Valida que los datos de la nota de débito, que tenga detalle y emisor definido
        $error = 0;
        $mensaje = '';
        $type = '';
        $titulo    = '';
        if (is_null($debit_note)) {
            $error = 1;
            $mensaje = 'La nota de débito seleccionada no se encuentra en la base de datos';
            $type = 'danger';
            $titulo = "Error <hr class=\"kv-alert-separator\">";
        }

        $items_exists = ItemDebitNote::find()->where(['debit_note_id' => $debit_note->id])->exists();
        if (!$items_exists) {
            $error = 1;
            $mensaje = 'La nota de débito seleccionada no contiene ninguna línea de producto / servicio. Por favor revise la información e inténtelo nuevamente';
            $type = 'warning';
            $titulo = "Advertencia <hr class=\"kv-alert-separator\">";
        }


        $configuracion = Issuer::find()->one();
        if (is_null($configuracion)) {
            $error = 1;
            $mensaje = 'No se ha podido obtener la información del emisor de la nota de débito. Por favor revise los datos e inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema';
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
        $debit_note = DebitNote::findOne($id);
        $emisor = Issuer::find()->one();
        $this->getEstadoFactura($debit_note, $emisor);
    }

    /**
     * @param DebitNote $debit_note
     * @param Issuer $emisor
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getEstadoFactura($debit_note, $emisor)
    {
        $debit_note_id = $debit_note->id;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $datos = $this->validaDatosFactura($debit_note);
        $error = $datos['error'];
        $actualizar = 0;
        $proceder = true;

        $status_hacienda = (int) $debit_note->status_hacienda;
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
                    $tipoDocumento = '02'; // NotaDebito
                    $datos = $apiConsultaHacienda->getEstado($debit_note, $emisor, $apiAccess->token, $tipoDocumento);
                    // En $datos queda el mensaje de respuesta
                    $apiAccess->CloseSesion($apiAccess->token, $emisor);
                    $actualizar = $datos['actualizar'];
                    $mensaje = $datos['mensaje'];
                    $type = $datos['type'];
                    $titulo = $datos['titulo'];
                    $estado = $datos['estado'];

                    if ($estado == 'rechazado') {
                        DebitNote::setStatusHacienda($debit_note_id, UtilsConstants::HACIENDA_STATUS_REJECTED); // Rechazada
                    } elseif ($estado == 'aceptado') {
                        DebitNote::setStatusHacienda($debit_note_id, UtilsConstants::HACIENDA_STATUS_ACCEPTED); // Aceptada
                    } elseif ($estado == 'recibido') {
                        DebitNote::setStatusHacienda($debit_note_id, UtilsConstants::HACIENDA_STATUS_RECEIVED); // Recibida
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
        $logo = "<img src=\"" . GlobalFunctions::BASE_URL. Setting::getUrlLogoBySettingAndType(1, Setting::SETTING_ID) . "\" width=\"100\"/>";

        if (!is_array($ids) && !empty($ids))
            $ids = explode(',', $ids);

        $debit_notes = DebitNote::find()->where(['id' => $ids])->all();
        $data = '';
        foreach ($debit_notes as $debit_note) {
            $qr_code_debit_note = $debit_note->generateQrCode();
            $img_qr = '<img src="' . $qr_code_debit_note . '" width="50"/>';

            $items_debit_note = ItemDebitNote::find()->where(['debit_note_id' => $debit_note->id])->all();

            if (!empty($data))
                $data .= '<pagebreak>';

            $data .= $this->renderPartial('_print_pdf', [
                'debit_note' => $debit_note,
                'items_debit_note' => $items_debit_note,
                'logo' => $logo,
                'moneda' => "COLONES",
                'original' => true,
                'img_qr' => $img_qr
            ]);

            $data .= '<pagebreak>';

            $data .= $this->renderPartial('_print_pdf', [
                'debit_note' => $debit_note,
                'items_debit_note' => $items_debit_note,
                'logo' => $logo,
                'moneda' => "COLONES",
                'original' => false,
                'img_qr' => $img_qr
            ]);
        }

        $html_header =  $this->renderPartial('_print_pdf_header', [
            'debit_note' => $debit_note,
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
            'filename' => "NotaDebito.pdf",
            'options' => [
                // any mpdf options you wish to set
                'title' => 'NotaDebito',
                'defaultheaderline' => 0,
                //'default_font' => 'Calibri',
                'setAutoTopMargin' => 'stretch',
                'showWatermarkText' => false,
            ],
            'methods' => [
                'SetTitle' => Yii::t('backend', 'NotaDebito'),
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
