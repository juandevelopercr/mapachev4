<?php

namespace backend\controllers;

use backend\components\ApiBCCR;
use backend\models\business\Adjustment;
use backend\models\business\CollectorHasInvoice;
use backend\models\business\Customer;
use backend\models\business\Invoice;
use backend\models\business\InvoiceDocuments;
use backend\models\business\InvoiceSearch;
use backend\models\business\ItemInvoice;
use backend\models\business\ItemInvoiceSearch;
use backend\models\business\Model;
use backend\models\business\PaymentMethodHasInvoice;
use backend\models\business\Product;
use backend\models\business\PurchaseOrder;
use backend\models\business\SellerHasInvoice;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\PaymentMethod;
use backend\models\nomenclators\UtilsConstants;
use backend\models\settings\Issuer;
use backend\models\settings\Setting;
use common\components\ApiV43\ApiAccess;
use common\components\ApiV43\ApiConsultaHacienda;
use common\components\ApiV43\ApiEnvioHacienda;
use common\components\ApiV43\ApiFirmadoHacienda;
use common\components\ApiV43\ApiXML;
use common\components\ftp\FtpImportTask;
use common\models\EnviarEmailForm;
use common\models\GlobalFunctions;
use common\models\User;
use kartik\mpdf\Pdf;
use Mpdf\QrCode\Output;
use Mpdf\QrCode\QrCode;
use Yii;
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * InvoiceController implements the CRUD actions for Invoice model.
 */
class InvoiceController extends Controller
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
     * Lists all Invoice models.
     * @return mixed
     */
    public function actionIndex()
    {        
        $is_point_sale = 0;
        $searchModel = new InvoiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $is_point_sale);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Invoice model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModelItems = new ItemInvoiceSearch(['invoice_id' => $id]);
        $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);

        return $this->render('view', [
            'model' => $model,
            'dataProviderItems' => $dataProviderItems,
        ]);
    }

    /**
     * Creates a new Invoice model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Invoice();
        $model->loadDefaultValues();
        $model->status = UtilsConstants::INVOICE_STATUS_PENDING;
        $model->status_hacienda = UtilsConstants::HACIENDA_STATUS_NOT_SENT;
        $model->change_type = ApiBCCR::getChangeTypeOfIssuer();
        $model->ready_to_send_email = 0;
        $model->email_sent = 0;
        $data = PaymentMethod::getSelectMap(false, '01');
        $defaulPayment = [];
        foreach ($data as $key => $value) {
            $defaulPayment[] = $key;
        }
        $model->payment_methods = $defaulPayment;

        $currency = Currency::findOne(['symbol' => 'CRC']);
        if ($currency !== null) {
            $model->currency_id = $currency->id;
        }

        $model->emission_date = date('Y-m-d H:i:s');
        $model->branch_office_id = User::getBranchOfficeIdOfActiveUser();
        $model->box_id = User::getBoxIdOfActiveUser();

        //$model->invoice_type = UtilsConstants::PRE_INVOICE_TYPE_INVOICE;
        $customer = Customer::find()->where(['code' => '000001'])->one();
        if ($customer !== null) {
            $model->customer_id = $customer->id;
            //$model->seller_id = (isset($customer->seller_id) && !empty($customer->seller_id)) ? $customer->seller_id : null;
            //$model->collector_id = (isset($customer->collector_id) && !empty($customer->collector_id)) ? $customer->collector_id : null;
            $model->route_transport_id = (isset($customer->route_transport_id) && !empty($customer->route_transport_id)) ? $customer->route_transport_id : null;
            $model->condition_sale_id = (isset($customer->condition_sale_id) && !empty($customer->condition_sale_id)) ? $customer->condition_sale_id : null;
            $model->credit_days_id = (isset($customer->credit_days_id) && !empty($customer->credit_days_id)) ? $customer->credit_days_id : null;
            $model->invoice_type = UtilsConstants::PRE_INVOICE_TYPE_TICKET;
        }

        $modelDocumentos = [new InvoiceDocuments];

        if ($model->load(Yii::$app->request->post())) {
            $model->emission_date = date('Y-m-d H:i:s');
            $model->consecutive = $model->generateConsecutive();
            $model->user_id = Yii::$app->user->id;
            $transaction = \Yii::$app->db->beginTransaction();

            try {
                if (Invoice::find()->select(['consecutive'])->where(['consecutive' => $model->consecutive])->exists()) {
                    $model->consecutive = $model->generateConsecutive();
                }

                $model->status_account_receivable_id = UtilsConstants::HACIENDA_STATUS_PENDING; // Para la gestión de cuentas por cobrar
                if ($model->condition_sale_id !== ConditionSale::getIdCreditConditionSale())
                    $model->pay_date = date('Y-m-d');

                $errors = 0;
                if ($model->contingency) {
                    $model->reference_code = '05'; //Sustituye comprobante provisional por $this->contingency
                    if (is_null($model->reference_number) || empty($model->reference_number)) {
                        $model->addError('reference_number', Yii::t('backend', 'Debe definir el número de referencia en los datos de contingencia'));
                        $errors++;
                    } elseif (is_null($model->reference_emission_date) || empty($model->reference_emission_date)) {
                        $model->addError('reference_emission_date', Yii::t('backend', 'Debe definir la fecha de emisión de referencia en los datos de contingencia'));
                        $errors++;
                    } elseif (is_null($model->reference_reason) || empty($model->reference_reason)) {
                        $model->addError('reference_emission_date', Yii::t('backend', 'Debe definir la razón de la contingencia'));
                        $errors++;
                    }
                }

                if ($errors > 0) {
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error creando el elemento'));

                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }

                if ($model->save()) {
                    PaymentMethodHasInvoice::updateRelation($model, [], 'payment_methods', 'payment_method_id');

                    SellerHasInvoice::updateRelation($model, [], 'sellers', 'seller_id');

                    CollectorHasInvoice::updateRelation($model, [], 'collectors', 'collector_id');


                    $modelDocumentos = Model::createMultiple(InvoiceDocuments::classname(), $modelDocumentos);
                    Model::loadMultiple($modelDocumentos, Yii::$app->request->post());

                    $uploadDir = Yii::getAlias('@backend') . "/web/uploads/documents/";

                    foreach ($modelDocumentos as $i => $modelDoc) {
                        $file[$i] = \yii\web\UploadedFile::getInstance($modelDoc, "[{$i}]documento");
                        $path[$i] = $uploadDir . $file[$i];
                        if ($file[$i]) {

                            $modelDoc->documento = $path[$i];

                            // file extension
                            $fileExt  = $file[$i]->extension;
                            // purge filename
                            $fileName = Yii::$app->security->generateRandomString();
                            //$fileName = GlobalFunctions::slugify($modelDoc->descripcion);
                            //$fileName = GlobalFunctions::slugify($modelDoc->descripcion).'-'.$model->consecutivo;

                            // set field to filename.extensions
                            $nombre_archivo = $fileName . ".{$fileExt}";

                            $file[$i]->saveAs($uploadDir . $nombre_archivo);
                            $modelDocumentos[$i]->documento = $nombre_archivo;
                        }
                    }

                    foreach ($modelDocumentos as $documento) {
                        $documento->invoice_id = $model->id;
                        //$documento->fecha = date('Y-m-d', strtotime($documento->fecha));
                        if (!($flag = $documento->save())) {
                            $errors = $documento->getErrors();
                            foreach ($errors as $key => $valor) {
                                if (!empty($msg))
                                    $msg .= '<br />';
                                $msg .= $valor[0];
                            }
                            //Yii::$app->session->setFlash('warning', $msg);
                            //break;
                        }
                    }

                    $transaction->commit();

                    GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Elemento creado correctamente'));

                    return $this->redirect(['update', 'id' => $model->id]);
                } else {
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error creando el elemento'));
                }
            } catch (Exception $e) {
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción creando el elemento'));
                $transaction->rollBack();
            }
        }

        return $this->render('create', [
            'model' => $model,
            'modelDocumentos' => empty($modelDocumentos) ? [new InvoiceDocuments] : $modelDocumentos,
        ]);
    }

    /**
     * Updates an existing Invoice model.
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
            GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'No es posible actualizar una factura enviada a hacienda'));
            return $this->redirect(['index']);
        }

        if (isset($model) && !empty($model)) {
            $searchModelItems = new ItemInvoiceSearch();
            $searchModelItems->invoice_id = $model->id;
            $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);
            if (is_null($model->status_hacienda)) {
                $model->status_hacienda = UtilsConstants::HACIENDA_STATUS_NOT_SENT;
            }

            //BEGIN payment method has invoice
            $payment_methods_assigned = PaymentMethodHasInvoice::getPaymentMethodByInvoiceId($id);

            $payment_methods_assigned_ids = [];
            foreach ($payment_methods_assigned as $value) {
                $payment_methods_assigned_ids[] = $value['payment_method_id'];
            }

            $model->payment_methods = $payment_methods_assigned_ids;
            //END payment method has invoice

            /*
            //BEGIN seller has seller
            $seller_assigned = SellerHasInvoice::getSellerByInvoiceId($id);

            $seller_assigned_ids = [];
            foreach ($seller_assigned as $value) {
                $seller_assigned_ids[] = $value['seller_id'];
            }

            $model->sellers = $seller_assigned_ids;
            //END seller method has seller


            //BEGIN collector has collector
            $collector_assigned = CollectorHasInvoice::getCollectorByInvoiceId($id);

            $collector_assigned_ids = [];
            foreach ($collector_assigned as $value) {
                $collector_assigned_ids[] = $value['collector_id'];
            }

            $model->collectors = $collector_assigned_ids;
            //END seller method has collector
            */

            // facturas documentos
            $modelDocumentos = InvoiceDocuments::find()->where(['invoice_id' => $model->id])->all();
            $modelDocumentoOld = InvoiceDocuments::find()->where(['invoice_id' => $model->id])->all();

            $old_status = (int)$model->status;


            if ($model->load(Yii::$app->request->post())) {
                $transaction = \Yii::$app->db->beginTransaction();
                $total_items = ItemInvoice::find()->where(['invoice_id' => $id])->count();
                $ready_to_send = (int) $model->ready_to_send_email;

                if ($ready_to_send === 1 && $total_items === 0) {
                    $model->ready_to_send_email = 0;
                    $model->addError('ready_to_send_email', 'No es posible marcar como "Lista para enviar" una factura sin items');
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'No es posible marcar como "Lista para enviar" una factura sin items'));

                    return $this->render('update', [
                        'model' => $model,
                        'searchModelItems' => $searchModelItems,
                        'dataProviderItems' => $dataProviderItems,
                        'modelDocumentos'=> empty($modelDocumentos) ? [new InvoiceDocuments]: $modelDocumentos,
                    ]);
                }

                try {
                    PaymentMethodHasInvoice::updateRelation($model, $payment_methods_assigned, 'payment_methods', 'payment_method_id');

                    //SellerHasInvoice::updateRelation($model, $seller_assigned, 'sellers', 'seller_id');

                    //CollectorHasInvoice::updateRelation($model, $collector_assigned, 'collectors', 'collector_id');

                    if ($model->save()) {
                        /*
                        $new_status = (int) $model->status;
                        if ($old_status !== $new_status) {
                            $model->verifyStock();
                        }
                            */

                        //Enviar factura por correo si aplica
                        /*
                        if ($old_email_sent === 0 && $old_ready_to_send === 0 && $old_ready_to_send !== $ready_to_send) {
                            $email_model = new EnviarEmailForm();
                            $issuer = Issuer::find()->one();
                            $email_model->id = $model->id;
                            $email_model->de = $issuer->email;
                            $email_model->para = $model->customer->email;
                            $email_model->nombrearchivo = $model->key . '.pdf';
                            $email_model->asunto = 'Envío de Factura Electrónica';
                            $response = $this->enviareamil($email_model, $model);

                            $model->email_sent = 1;
                            $model->save();
                        }
                            */


                        // Documentos
                        $oldIDs = ArrayHelper::map($modelDocumentos, 'id', 'id');
                        $modelDocumentos = Model::createMultiple(InvoiceDocuments::classname(), $modelDocumentos);
                        Model::loadMultiple($modelDocumentos, Yii::$app->request->post());
                        $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($modelDocumentos, 'id', 'id')));
                        $deletedOldIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($modelDocumentoOld, 'id', 'id')));

                        $uploadDir = Yii::getAlias('@backend') . "/web/uploads/documents/";

                        if (!empty($deletedIDs))
                        {
                            InvoiceDocuments::deleteAll(['id' => $deletedIDs]);
                        }

                        foreach ($modelDocumentos as $i => $modelDoc) {
                            $file[$i] = \yii\web\UploadedFile::getInstance($modelDoc, "[{$i}]documento");
                            $path[$i] = $uploadDir . $file[$i];                            
                            if ($file[$i]) {

                                $modelDoc->documento = $path[$i];
                                // file extension
                                $fileExt  = $file[$i]->extension;
                                // purge filename
                                $fileName = Yii::$app->security->generateRandomString();
                                //$fileName = GlobalFunctions::slugify($modelDoc->descripcion).'-'.$model->consecutivo;

                                // set field to filename.extensions
                                $nombre_archivo = $fileName . ".{$fileExt}";

                                $file[$i]->saveAs($uploadDir . $nombre_archivo);
                                $modelDocumentos[$i]->documento = $nombre_archivo;
                                $modelDocumentos[$i]->invoice_id = $model->id;
                                //die(var_dump($documento));
                            } else {
                                $modelDocumentos[$i]->documento = $modelDocumentoOld[$i]->documento;
                            }
                        }

                        
						foreach ($modelDocumentos as $documento) {
							$documento->invoice_id = $model->id;
							//$documento->adjuntar_a_factura = (int)$documento->adjuntar_a_factura;
							//$documento->fecha = date('Y-m-d', strtotime($documento->fecha));
							
							if (!($flag = $documento->save())) {
								$errors = $documento->getErrors();
								foreach ($errors as $key => $valor) {
									if (!empty($msg))
										$msg .= '<br />';
									$msg .= $valor[0];
								}				
								Yii::$app->session->setFlash('warning', $msg);
								break;
							}
						}

                        $transaction->commit();
                        /*
                        $datos = $this->validaDatosFactura($model);                      
                        $error = $datos['error'];

                        if ($error == 0){
                            $result = UtilsConstants::sendInvoiceToHacienda($model->id);
                            GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento actualizado correctamente'));
                        }
                        else
                        {
                            GlobalFunctions::addFlashMessage('warning',Yii::t('backend','Elemento actualizado correctamente. Existen errores en la factura, corrijalos e intentelo nuevamente. La factura no se ha enviado hacienda.'));
                        }
                        */
                        GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Elemento actualizado correctamente.'));
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
            'modelDocumentos'=> empty($modelDocumentos) ? [new InvoiceDocuments]: $modelDocumentos,
        ]);
    }

    /**
     * Deletes an existing Invoice model.
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
     * Finds the Invoice model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Invoice the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $a = Invoice::findOne($id);
        if (($model = Invoice::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend', 'La página solicitada no existe'));
        }
    }

    public function actionGetResumeInvoice($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = Invoice::getResumeInvoice($id);

        //$total_price = $model->subtotal + $model->tax_amount;

        return \Yii::$app->response->data = [
            'total_subtotal' => GlobalFunctions::formatNumber($model->subtotal, 2),
            'total_tax' => GlobalFunctions::formatNumber($model->tax_amount, 2),
            'total_discount' => GlobalFunctions::formatNumber($model->discount_amount, 2),
            'total_exonerate' => GlobalFunctions::formatNumber($model->exonerate_amount, 2),
            'total_price' => GlobalFunctions::formatNumber($model->price_total, 2),
        ];
    }
    
    public function actionDeleteDocument($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $invoiceDocument = InvoiceDocuments::find()->where(['id'=>$id])->one();
        if (!is_null($invoiceDocument)){
            $uploadDir = Yii::getAlias('@backend') . "/web/uploads/documents/".$invoiceDocument->documento;
            if (file_exists($uploadDir))
                unlink($uploadDir);
        }
        return true;
    }

    public function showPdf($ids, $original, $moneda = 'COLONES', $destino = 'browser', $filename = 'Factura')
    {
        $logo = "<img src=\"" . GlobalFunctions::BASE_URL . Setting::getUrlLogoBySettingAndType(1, Setting::SETTING_ID) . "\" width=\"165\"/>";
        $configuracion = Setting::find()->where(['id' => 1])->one();
        $textCuentas = $configuracion->bank_information;

        if (!is_array($ids) && !empty($ids))
            $ids = explode(',', $ids);

        $invoices = Invoice::find()->where(['id' => $ids])->all();
        $data = '';
        foreach ($invoices as $invoice) {
            $qr_code_invoice = $invoice->generateQrCode();
            $img_qr = '<img src="' . $qr_code_invoice . '" width="100"/>';

            $items_invoice = ItemInvoice::find()->where(['invoice_id' => $invoice->id])->all();

            if (!empty($data))
                $data .= '<pagebreak>';

            $data .= $this->renderPartial('_pdf', [
                'invoice' => $invoice,
                'items_invoice' => $items_invoice,
                'logo' => $logo,
                'moneda' => $moneda,
                'original' => $original,
                'img_qr' => $img_qr,
                'textCuentas' => $textCuentas,
            ]);
        }

        $html_header =  $this->renderPartial('_pdf_header', [
            'invoice' => $invoice,
            'logo' => $logo,
            'moneda' => $moneda,
            'original' => $original,
        ]);

        if ($destino == 'browser') {
            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'destination' => Pdf::DEST_BROWSER,
                'orientation' => Pdf::ORIENT_PORTRAIT, // ORIENT_PORTRAIT
                'content' => $data,
                'filename' => $filename,
                'options' => [
                    // any mpdf options you wish to set
                    'title' => 'Factura',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => $invoice->IsCanceled(),
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend', 'Facturas'),
                    'SetWatermarkText' => 'ANULADO',
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    'SetFooter' => ['|' . Yii::t('backend', 'Página') . ' {PAGENO}|'],
                ],
            ]);

            $pdf->marginTop = 40;
            $mpdf = $pdf->getApi();
            $mpdf->SetHTMLHeader($html_header);

            $pdf->marginBottom = 0;
            $pdf->marginHeader = 0;
            $pdf->marginFooter = 0;
            $pdf->defaultFontSize = 12;

            return $pdf->render();
        } else {
            if (!file_exists("uploads/invoice/") || !is_dir("uploads/invoice/")) {
                try {
                    FileHelper::createDirectory("uploads/invoice/", 0777);
                } catch (\Exception $exception) {
                    Yii::info("Error handling Factura folder resources");
                }
            }

            $file_pdf_save = Yii::getAlias('@backend') . '/web/uploads/invoice/' . $filename;

            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'destination' => Pdf::DEST_FILE,
                'orientation' => Pdf::ORIENT_PORTRAIT, // ORIENT_PORTRAIT
                'content' => $data,
                'filename' => $file_pdf_save,
                'options' => [
                    // any mpdf options you wish to set
                    'title' => 'Factura',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => $invoice->IsCanceled(),
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend', 'Facturas'),
                    'SetWatermarkText' => 'ANULADO',
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    'SetFooter' => ['|' . Yii::t('backend', 'Página') . ' {PAGENO}|'],
                ],
            ]);

            $pdf->marginTop = 40;
            $mpdf = $pdf->getApi();
            $mpdf->SetHTMLHeader($html_header);

            $pdf->marginBottom = 0;
            $pdf->marginHeader = 0;
            $pdf->marginFooter = 0;
            $pdf->defaultFontSize = 12;

            $pdf->render();

            return $file_pdf_save;
        }
    }

    public function showTicketPdf($ids, $original, $moneda = 'COLONES', $destino = 'browser', $filename = 'Tiquete', $show_logo = true)
    {
        if ($show_logo) {
            $logo = "<img src=\"" . GlobalFunctions::BASE_URL . Setting::getUrlLogoBySettingAndType(1, Setting::SETTING_ID) . "\" width=\"100\"/>";
        } else {
            $logo = '';
        }
        $configuracion = Setting::find()->where(['id' => 1])->one();
        $textCuentas = $configuracion->bank_information;

        if (!is_array($ids) && !empty($ids))
            $ids = explode(',', $ids);

        $invoices = Invoice::find()->where(['id' => $ids])->all();
        $data = '';
        if ($show_logo) {
            $pivot = $max_dynamic_height = 250;
        } else {
            $pivot = $max_dynamic_height = 235;
        }

        foreach ($invoices as $invoice) {
            $dynamic_height = $pivot;

            $qr_code_invoice = $invoice->generateQrCode();
            $img_qr = "<img src=\"" . $qr_code_invoice . "\"/>";

            $items_invoice = ItemInvoice::find()->where(['invoice_id' => $invoice->id])->all();
            $total_items = count($items_invoice);
            $dynamic_height += ($total_items * 8);
            if ($dynamic_height > $max_dynamic_height) {
                $max_dynamic_height = $dynamic_height;
            }

            if (!empty($data))
                $data .= '<pagebreak>';

            $data .= $this->renderPartial('_ticket_pdf', [
                'invoice' => $invoice,
                'items_invoice' => $items_invoice,
                'logo' => $logo,
                'moneda' => $moneda,
                'original' => $original,
                'img_qr' => $img_qr,
                'textCuentas' => $textCuentas,
            ]);
        }

        if ($destino == 'browser') {
            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'destination' => Pdf::DEST_BROWSER,
                'content' => $data,
                'filename' => $filename,
                'options' => [
                    // any mpdf options you wish to set
                    'title' => 'Tiquete',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => $invoice->IsCanceled(),
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend', 'Tiquetes'),
                    'SetWatermarkText' => 'ANULADO',
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                ],
            ]);


            $pdf->format = [76.2, $max_dynamic_height];
            $pdf->marginLeft = 2.5;
            $pdf->marginRight = 2.5;
            $pdf->marginTop = 5;
            $pdf->marginBottom = 0;
            $pdf->marginHeader = 0;
            $pdf->marginFooter = 0;
            $pdf->defaultFontSize = 13;

            return $pdf->render();
        } else {
            if (!file_exists("uploads/invoice/") || !is_dir("uploads/invoice/")) {
                try {
                    FileHelper::createDirectory("uploads/invoice/", 0777);
                } catch (\Exception $exception) {
                    Yii::info("Error handling Factura folder resources");
                }
            }

            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'destination' => Pdf::DEST_FILE,
                'content' => $data,
                'filename' => $filename,
                'options' => [
                    // any mpdf options you wish to set
                    'title' => 'Factura',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => $invoice->IsCanceled(),
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend', 'Facturas'),
                    'SetWatermarkText' => 'ANULADO',
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    'SetFooter' => ['|' . Yii::t('backend', 'Página') . ' {PAGENO}|'],
                ],
            ]);

            $pdf->format = [76.2, $max_dynamic_height];
            $pdf->marginLeft = 2.5;
            $pdf->marginRight = 2.5;
            $pdf->marginTop = 5;
            $pdf->marginBottom = 0;
            $pdf->marginHeader = 0;
            $pdf->marginFooter = 0;
            $pdf->defaultFontSize = 13;

            $pdf->render();

            return Url::to($filename);
        }
    }

    public function actionViewpdf($id)
    {
        $invoice = $this->findModel($id);
        $type = (int) $invoice->invoice_type;
        if ($type === UtilsConstants::PRE_INVOICE_TYPE_INVOICE) {
            return $this->showPdf($id, true);
        } else {
            return $this->showTicketPdf($id, true);
        }
    }

    public function actionViewticketpdf($id)
    {
        return $this->showTicketPdf($id, true, 'COLONES',  'browser',  'Tiquete', false);
    }

    public function actionImpTickets($ids)
    {
        return $this->showTicketPdf($ids, true, 'COLONES',  'browser',  'Tiquete', false);
    }

    public function actionViewxml($id)
    {                
        $invoice = Invoice::find()->where(['id' => $id])->one();
        $items_invoice = ItemInvoice::find()->where(['invoice_id' => $id])->all();

        $apiXML = new ApiXML();
        $issuer = Issuer::find()->one();
        $xml = $apiXML->genXMLFe($issuer, $invoice, $items_invoice);

        $p12Url = $issuer->getFilePath();
        $pinP12 = $issuer->certificate_pin;

        $doc_type = '01'; // Factura
        $apiFirma = new ApiFirmadoHacienda();
        $xmlFirmado = $apiFirma->firmar($p12Url, $pinP12, $xml, $doc_type);

        $xml = base64_decode($xmlFirmado);

        // http response
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_RAW;
        $headers = Yii::$app->response->headers;
        $headers->add('Content-Type', 'text/xml');
        $headers->add('Content-Disposition', 'attachment; filename=' . 'FE-' . $invoice->key . ".xml");
        return $xml;
    }

    public function actionViewpdfcontingencia($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;

        $logo = "<img src=\"" . GlobalFunctions::BASE_URL . Setting::getUrlLogoBySettingAndType(1, Setting::SETTING_ID) . "\" width=\"100\"/>";

        $data = $this->renderPartial('_pdf_contingencia', [
            'logo' => $logo,
        ]);

        $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE,
            'destination' => Pdf::DEST_BROWSER,
            'content' => $data,
            'filename' => 'PDF_Contingencia',
            'options' => [
                // any mpdf options you wish to set
                'title' => 'PDF_Contingencia',
                'defaultheaderline' => 0,
                //'default_font' => 'Calibri',
                'setAutoTopMargin' => 'stretch',
                'showWatermarkText' => false,
            ],
            'methods' => [
                'SetTitle' => Yii::t('backend', 'PDF_Contingencia'),
                'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                'SetFooter' => ['|' . Yii::t('backend', 'Página') . ' {PAGENO}|'],
            ],
        ]);

        $pdf->format = [216, 280];
        $pdf->marginTop = 5;

        return $pdf->render();
    }

    public function actionEnviarFacturaEmail($id)
    {
        $model = new EnviarEmailForm();
        $model->id = $id;
        $msg = '';
        if ($model->load(Yii::$app->request->post())) {
            $model->id = Yii::$app->request->post()['EnviarEmailForm']['id'];

            $factura = Invoice::find()->where(['id' => $id])->one();
            $model->nombrearchivo = '';
            $model->nombrearchivo .= $factura->key . '.pdf';
            $model->cc = Yii::$app->request->post()['EnviarEmailForm']['cc'];
            $model->cuerpo = Yii::$app->request->post()['EnviarEmailForm']['cuerpo'];

            $respuesta = $this->enviareamil($model, $factura);

            Yii::$app->response->format = 'json';
            if ($respuesta == UtilsConstants::SEND_MAIL_RESPONSE_TYPE_SUCCESS) {
                $factura->ready_to_send_email = 1;
                $factura->email_sent = 1;
                $factura->save(false);

                $msg .= 'Se ha enviado la factura por correo electrónico';
                $type = 'success';
            } else
            if ($respuesta == UtilsConstants::SEND_MAIL_RESPONSE_TYPE_ERROR || UtilsConstants::SEND_MAIL_RESPONSE_TYPE_EXCEPTION) {
                $msg .= 'Ha ocurrido un error. No se ha podido enviar el correo electrónico';
                $type = 'danger';
            }

            return \Yii::$app->response->data  = [
                'message' => $msg,
                'type' => $type,
                'titulo' => "Informaci&oacute;n <hr class=\"kv-alert-separator\">",
            ];
        } else {
            $factura = Invoice::find()->where(['id' => $id])->one();
            $emisor = Issuer::find()->one();
            $model->de = $emisor->email;
            $model->para = $factura->customer->email;
            $model->cc = $factura->customer->email_cc;
            $model->nombrearchivo = $factura->key . '.pdf';
            $model->asunto = 'Envío de Factura Electrónica';

            return $this->renderAjax('_emailForm', [
                'model' => $model,
            ]);
        }
    }

    public function enviareamil($model, $invoice)
    {
        $user = yii::$app->user->identity;
        $respuesta = false;

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

            $respuesta = $invoice->sendEmail($model->asunto, $to, $arr_cc, $model->cuerpo);
        }

        return $respuesta;
    }

    public function actionEnviarFacturaHacienda($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $factura = Invoice::findOne($id);

        //$factura->verifyStock();

        $datos = $this->validaDatosFactura($factura);
        $emisor = Issuer::find()->one();
        $error = $datos['error'];
        if ($error == 0) {


            if (is_null($factura->consecutive) || empty($factura->consecutive)) {
                // Se genera consecutive
                $factura->consecutive = $factura->generateConsecutive();

                // Se genera key
                $factura->key = '506' . date('d') . date('m') . date('y');
                // La identificación debe tener una longitud de 12 digitos, completar con 0
                $factura->key .= str_pad($emisor->identification, 12, '0', STR_PAD_LEFT);

                $factura->key .= $factura->consecutive;
                // Un digito para situación del comprobante electrónico
                // 1  Normal: Comprobantes electrónicos que son generados y transmitidos en el mismo acto de compra-venta y prestación del servicio al sistema de validación de comprobantes electrónicos de la Dirección General de Tributación de Costa Rica.
                // 2  Contingencia:	Comprobantes electrónicos que sustituyen al comprobante físico emitido por contingencia.
                // 3  Sin internet:	Comprobantes que han sido generados y expresados en formato electrónico, pero no se cuenta con el respectivo acceso a internet para el envío inmediato de los mismos a la Dirección General de Tributación de Costa Rica.
                $factura->key .= '1';
                // Los restantes dígitos son un código de seguridad generados por el sistema nuestro
                $factura->key .= date('Y') . date('m') . date('d');

                $factura->save();
            }

            // Si todas las validaciones son correctas, proceder al proceso
            // Logearse en la api y obtener el token;
            $apiAccess = new ApiAccess();
            $datos = $apiAccess->loginHacienda($emisor);

            $error = $datos['error'];
            if ($error == 0) {
                // Obtener el xml firmado electrónicamente
                $invoice = Invoice::find()->where(['id' => $id])->one();
                $items_invoice = ItemInvoice::find()->where(['invoice_id' => $id])->all();

                $apiXML = new ApiXML();
                $issuer = Issuer::find()->one();
                $xml = $apiXML->genXMLFe($issuer, $invoice, $items_invoice);

                $p12Url = $issuer->getFilePath();
                $pinP12 = $issuer->certificate_pin;

                $doc_type = '01'; // Factura
                $apiFirma = new ApiFirmadoHacienda();
                $xmlFirmado = $apiFirma->firmar($p12Url, $pinP12, $xml, $doc_type);

                $xml = base64_decode($xmlFirmado);

                // Enviar documento a hacienda
                $apiEnvioHacienda = new ApiEnvioHacienda();
                $datos = $apiEnvioHacienda->send($xmlFirmado, $apiAccess->token, $factura, $emisor, $doc_type);

                // En $datos queda el mensaje de respuesta
                //die(var_Dump($datos));
                $respuesta = $datos['response'];
                $code = $respuesta->getHeaders()->get('http-code');
                if ($code == '202' || $code == '201' || $code == '200') {
                    $mensaje = "La factura electrónica con clave: [" . $factura->key . "] se recibió correctamente, queda pendiente la validación de esta y el envío de la respuesta de parte de Hacienda.";
                    $factura->status_hacienda = UtilsConstants::HACIENDA_STATUS_RECEIVED; // Recibido
                    $factura->save(false);
                    $type = 'success';
                    $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
                } elseif ($code == '400') {
                    $error = 1;
                    $mensaje = utf8_encode($respuesta->getHeaders()->get('X-Error-Cause'));
                    $type = 'danger';
                    $titulo = "Error <hr class=\"kv-alert-separator\">";
                } else {
                    $error = 1;
                    $mensaje = "Ha ocurrido un error desconocido al enviar la factura electrónica con clave: [" . $factura->key . "]. Póngase en contacto con el administrador del sistema";
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

    public function validaDatosFactura($factura)
    {
        // Valida que los datos de la factura, que tenga detalle y emisor definido
        $error = 0;
        $mensaje = '';
        $type = '';
        $titulo    = '';
        if (is_null($factura)) {
            $error = 1;
            $mensaje = 'La factura seleccionada no se encuentra en la base de datos';
            $type = 'danger';
            $titulo = "Error <hr class=\"kv-alert-separator\">";
        }

        $items_exists = ItemInvoice::find()->where(['invoice_id' => $factura->id])->exists();
        if (!$items_exists) {
            $error = 1;
            $mensaje = 'La factura seleccionada no contiene ninguna línea de producto / servicio. Por favor revise la información e inténtelo nuevamente';
            $type = 'warning';
            $titulo = "Advertencia <hr class=\"kv-alert-separator\">";
        }


        $configuracion = Issuer::find()->one();
        if (is_null($configuracion)) {
            $error = 1;
            $mensaje = 'No se ha podido obtener la información del emisor de la factura. Por favor revise los datos e inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema';
            $type = 'danger';
            $titulo = "Error <hr class=\"kv-alert-separator\">";
        }
        return ['error' => $error, 'mensaje' => $mensaje, 'type' => $type, 'titulo' => $titulo];
    }

    public function actionGetEstadoFacturaHacienda($id)
    {
        $factura = Invoice::findOne($id);
        $emisor = Issuer::find()->one();
        $this->getEstadoFactura($factura, $emisor);
    }

    /**
     * @param Invoice $factura
     * @param Issuer $emisor
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getEstadoFactura($factura, $emisor)
    {
        $invoice_id = $factura->id;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $datos = $this->validaDatosFactura($factura);
        $error = $datos['error'];
        $actualizar = 0;
        $proceder = true;

        $status_hacienda = (int) $factura->status_hacienda;
        if ($status_hacienda === UtilsConstants::HACIENDA_STATUS_RECEIVED || $status_hacienda === UtilsConstants::HACIENDA_STATUS_REJECTED) {
            if ($error == 0 && $proceder == true) {
                // Si todas las validaciones son correctas, proceder al proceso
                // Logearse en la api y obtener el token;
                $apiAccess = new ApiAccess();
                $datos = $apiAccess->loginHacienda($emisor);
                $error = $datos['error'];
                if ($error == 0) {
                    // consultar estado de documento en hacienda
                    $apiConsultaHacienda = new ApiConsultaHacienda();
                    $tipoDocumento = '01'; // Factura
                    $datos = $apiConsultaHacienda->getEstado($factura, $emisor, $apiAccess->token, $tipoDocumento);
                    // En $datos queda el mensaje de respuesta
                    $apiAccess->CloseSesion($apiAccess->token, $emisor);
                    $actualizar = $datos['actualizar'];
                    $mensaje = $datos['mensaje'];
                    $type = $datos['type'];
                    $titulo = $datos['titulo'];
                    $estado = $datos['estado'];

                    if ($estado == 'aceptado') {
                        $invoice = Invoice::findOne($invoice_id);
                        Invoice::setStatusHacienda($invoice_id, UtilsConstants::HACIENDA_STATUS_ACCEPTED); // Aceptada
                        //if ($invoice->status_hacienda != UtilsConstants::HACIENDA_STATUS_ACCEPTED)
                        if ($invoice->status_hacienda == UtilsConstants::HACIENDA_STATUS_RECEIVED) {
                            // Extraer del inventario  
                            $enviar = true;
                            $items_associates = ItemInvoice::findAll(['invoice_id' => $invoice_id]);
                            foreach ($items_associates as $index => $item) {
                                if (isset($item->product_id) && !empty($item->product_id)) {
                                    if ($invoice->invoice_type == UtilsConstants::PRE_INVOICE_TYPE_INVOICE)
                                        $tipo = 'de la Factura Electrónica';
                                    else
                                        $tipo = 'del Tiquete Electrónico';

                                    $observations = 'Salida por aceptación en hacienda ' . $tipo . ' # ' . $invoice->consecutive;
                                    $adjustment_type = UtilsConstants::ADJUSTMENT_TYPE_INVOICE_SALES;

                                    // Chequear si ya se ha realizado ese ajuste
                                    $adjustment = Adjustment::find()->where([
                                        'product_id' => $item->product_id, 'key' => $invoice->key, 'type' => $adjustment_type,
                                        'origin_branch_office_id' => $invoice->branch_office_id, 'observations' => $observations
                                    ])->one();
                                    if (is_null($adjustment)) {
                                        // Se calcula la cantidad a extraer según la unidad de medidad del producto
                                        $quantity = Product::getUnitQuantityByItem($item->product_id, $item->quantity, $item->unit_type_id);

                                        Product::extractInventory($item->product_id, $adjustment_type, $invoice->branch_office_id, $quantity, $invoice->id, $observations, $invoice->key);
                                    } else
                                        $enviar = false;
                                }
                            }
                            if ($invoice->invoice_type == UtilsConstants::PRE_INVOICE_TYPE_INVOICE && $enviar == true) {
                                // Enviar documentos por emails
                                $subject = Yii::t('backend', 'Factura electrónica #' . $invoice->consecutive);
                                $email = $invoice->customer->email;
                                $email_cc = UtilsConstants::getListaEmailsByEmailString($invoice->customer->email_cc);
                                $body = '';
                                $invoice->sendEmail($subject, $email, $email_cc, $body);
                            }
                        }
                    } else
                    if ($estado == 'rechazado') {
                        // Devolver el inventario 
                        /*
                        $invoice = Invoice::findOne($invoice_id);
                        $items_associates = ItemInvoice::findAll(['invoice_id' => $invoice_id]);
                        foreach ($items_associates as $index => $item) {
                            if (isset($item->product_id) && !empty($item->product_id)) {
                                $old_quantity = $item->quantity;

                                $unit_type_code = $item->unitType->code;

                                if ($unit_type_code == 'CAJ' || $unit_type_code == 'CJ') {
                                    if (isset($item->product->quantity_by_box)) {
                                        $old_quantity *= $item->product->quantity_by_box;
                                    }
                                } elseif ($unit_type_code == 'BULT' || $unit_type_code == 'PAQ') {
                                    if (isset($item->product->package_quantity)) {
                                        $old_quantity *= $item->product->package_quantity;
                                    }
                                }
                                if ($invoice->invoice_type == UtilsConstants::PRE_INVOICE_TYPE_INVOICE)
                                    $tipo = 'de la Factura Electrónica';
                                else
                                    $tipo = 'del Tiquete Electrónico';

                                $observations = 'Devolución por rechazo en hacienda ' . $tipo . ' # ' . $invoice->consecutive;
                                $adjustment_type = UtilsConstants::ADJUSTMENT_TYPE_INVOICE_SALES;
                                Product::returnToInventory($item->product_id, $adjustment_type, $invoice->branch_office_id, $old_quantity, $invoice->id, false, $observations);
                            }
                        }
                        */
                    }
                } else {
                    $mensaje = 'No se ha podido autenticar en la api de hacienda. Inténtelo nuevamente';
                    $type = 'danger';
                    $titulo = "Error <hr class=\"kv-alert-separator\">";
                }
            }
        } else {
            $mensaje = 'Solo se puede consultar el estado de una factura que haya sido recibida en hacienda';
            $type = 'warning';
            $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
            $actualizar = 0;
        }

        return \Yii::$app->response->data  =  ['mensaje' => $mensaje, 'type' => $type, 'titulo' => $titulo, 'actualizar' => $actualizar];
    }

    public function actionCorrection($id)
    {
        $model = new Invoice();
        $model->LoadDefaultValues();

        $factura = Invoice::findOne($id);
        $model->attributes = $factura->attributes;
        $model->response_xml = '';
        $model->correct_invoice = 1;
        $model->correct_invoice_id = $id;
        $model->status_hacienda = UtilsConstants::HACIENDA_STATUS_NOT_SENT;
        $model->reference_number = $factura->key;
        $model->reference_emission_date = date('d-M-Y');
        $model->emission_date = date('d-M-Y');
        $model->reference_code = '01';
        $model->reference_reason = 'Anula documento de referencia';
        $model->consecutive = $model->generateConsecutive();
        $model->key = $model->generateKey();
        $model->sellers = [];
        $model->collectors = [];

        //BEGIN payment method has purchase_order
        $payment_methods_assigned = PaymentMethodHasInvoice::getPaymentMethodByInvoiceId($id);

        $payment_methods_assigned_ids = [];
        foreach ($payment_methods_assigned as $value) {
            $payment_methods_assigned_ids[] = $value['payment_method_id'];
        }

        $model->payment_methods = $payment_methods_assigned_ids;
        //END payment method has purchase_order



        //BEGIN seller has seller
        $seller_assigned = SellerHasInvoice::getSellerByInvoiceId($id);

        $seller_assigned_ids = [];
        foreach ($seller_assigned as $value) {
            $seller_assigned_ids[] = $value['seller_id'];
        }

        $model->sellers = $seller_assigned_ids;
        //END seller method has seller



        //BEGIN collector has collector
        $collector_assigned = CollectorHasInvoice::getCollectorByInvoiceId($id);

        $collector_assigned_ids = [];
        foreach ($collector_assigned as $value) {
            $collector_assigned_ids[] = $value['collector_id'];
        }

        $model->collectors = $collector_assigned_ids;
        //END seller method has collector   

        $searchModelItems = new ItemInvoiceSearch();
        $searchModelItems->invoice_id = $model->id;
        $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);

        if ($model->load(Yii::$app->request->post())) {
            $transaction = \Yii::$app->db->beginTransaction();

            $model->reference_emission_date = date('Y-m-d H:i:s');
            $model->emission_date = date('Y-m-d H:i:s');

            try {
                $errors = 0;
                if (empty($model->reference_emission_date)) {
                    $model->addError('reference_emission_date', 'Debe especificar la fecha de emisión');
                    $errors++;
                }

                if (empty($model->reference_reason)) {
                    $model->addError('reference_reason', 'Debe especificar la razón por la cual se corrige la factura');
                    $errors++;
                }

                if ($errors > 0) {
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error actualizando el elemento'));

                    return $this->render('correction', [
                        'model' => $model,
                        'searchModelItems' => $searchModelItems,
                        'dataProviderItems' => $dataProviderItems,
                    ]);
                }

                if (Invoice::find()->select(['consecutive'])->where(['consecutive' => $model->consecutive])->exists()) {
                    $model->consecutive = $model->generateConsecutive();
                }

                $model->generateKey();

                if ($model->save()) {

                    PaymentMethodHasInvoice::updateRelation($model, [], 'payment_methods', 'payment_method_id');

                    SellerHasInvoice::updateRelation($model, [], 'sellers', 'seller_id');

                    CollectorHasInvoice::updateRelation($model, [], 'collectors', 'collector_id');

                    //clonar los items de la proforma y asociarlos a la nueva
                    $items_associates = ItemInvoice::findAll(['invoice_id' => $id]);

                    foreach ($items_associates as $index => $item) {
                        $new_item = new ItemInvoice();
                        $new_item->attributes = $item->attributes;
                        $new_item->invoice_id = $model->id;
                        $new_item->save();
                    }

                    $items_documents = InvoiceDocuments::findAll(['invoice_id' => $id]);
                    foreach ($items_documents as $index => $item) {
                        $new_item = new InvoiceDocuments();
                        $new_item->attributes = $item->attributes;
                        $new_item->invoice_id = $model->id;
                        $new_item->save();
                    }

                    $factura->status_hacienda = UtilsConstants::HACIENDA_STATUS_ANULATE;
                    $factura->save(false);

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

        return $this->render('correction', [
            'model' => $model,
            'searchModelItems' => $searchModelItems,
            'dataProviderItems' => $dataProviderItems,
        ]);
    }

    public function printPdf($ids)
    {
        $logo = "<img src=\"" . GlobalFunctions::BASE_URL . Setting::getUrlLogoBySettingAndType(1, Setting::SETTING_ID) . "\" width=\"100\"/>";
        $configuracion = Setting::find()->where(['id' => 1])->one();
        $textCuentas = $configuracion->bank_information;

        if (!is_array($ids) && !empty($ids))
            $ids = explode(',', $ids);

        $invoices = Invoice::find()->where(['id' => $ids])->all();
        $data = '';
        foreach ($invoices as $invoice) {
            $qr_code_invoice = $invoice->generateQrCode();
            $img_qr = '<img src="' . $qr_code_invoice . '" width="50"/>';

            $items_invoice = ItemInvoice::find()->where(['invoice_id' => $invoice->id])->all();

            if (!empty($data))
                $data .= '<pagebreak>';

            $data .= $this->renderPartial('_print_pdf', [
                'invoice' => $invoice,
                'items_invoice' => $items_invoice,
                'logo' => $logo,
                'moneda' => "COLONES",
                'original' => true,
                'img_qr' => $img_qr,
                'textCuentas' => $textCuentas,
            ]);

            $data .= '<pagebreak>';

            $data .= $this->renderPartial('_print_pdf', [
                'invoice' => $invoice,
                'items_invoice' => $items_invoice,
                'logo' => $logo,
                'moneda' => "COLONES",
                'original' => false,
                'img_qr' => $img_qr,
                'textCuentas' => $textCuentas,
            ]);
        }

        $html_header =  $this->renderPartial('_print_pdf_header', [
            'invoice' => $invoice,
            'logo' => $logo,
            'moneda' => "COLONES",
            'original' => true,
        ]);


        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE,
            //'orientation' => Pdf::ORIENT_LANDSCAPE,
            'orientation' => Pdf::ORIENT_PORTRAIT, // ORIENT_PORTRAIT
            'destination' => Pdf::DEST_BROWSER,
            'content' => $data,
            'filename' => "Factura.pdf",
            'options' => [
                // any mpdf options you wish to set
                'title' => 'Factura',
                'defaultheaderline' => 0,
                //'default_font' => 'Calibri',
                'setAutoTopMargin' => 'stretch',
                'showWatermarkText' => false,
            ],
            'methods' => [
                'SetTitle' => Yii::t('backend', 'Factura'),
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

    /**
     * Funcion para generar el pdf de Preparación de mercancías
     */
    public function actionPreparation_pdf($ids)
    {
        if (!is_array($ids) && !empty($ids))
            $ids = explode(',', $ids);

        $items = Invoice::find()
            ->select([
                'SUM(item_invoice.quantity) AS quantity',
                'item_invoice.unit_type_id AS unit_type_id',
                'unit_type.code AS unit_type',
                'item_invoice.code',
                'product.description',
                'product.id AS product_id',
                'product.quantity_by_box AS quantity_by_box',
                'product.package_quantity AS package_quantity',
            ])
            ->where(['invoice.id' => $ids])
            ->innerJoin('item_invoice', 'item_invoice.invoice_id = invoice.id')
            ->innerJoin('product', 'product.id = item_invoice.product_id')
            ->innerJoin('unit_type', 'unit_type.id = item_invoice.unit_type_id')
            ->groupBy(
                ' 
                product.id,
                item_invoice.unit_type_id,
                unit_type.code,
                item_invoice.code'
            )
            ->orderBy('product.id')
            ->asArray()
            ->all();


        $logo = '<img src="' . GlobalFunctions::BASE_URL . Setting::getUrlLogoBySettingAndType(1) . '" width="75" height="45">';

        $content = $this->renderPartial('_pdf_preparation', [
            'items' => $items
        ]);

        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE,
            'destination' => Pdf::DEST_BROWSER,
            'content' => $content,
            //'cssFile' => '@backend/web/css/reportes-pdf.css',
            'filename' => 'PreparaciónDeMercancías.' . date('Y-m-d') . 'pdf',
            'options' => [
                // any mpdf options you wish to set
                'title' => 'Preparación de mercancías',
                'defaultheaderline' => 0,
                //'default_font' => 'Calibri',
                'setAutoTopMargin' => 'stretch',
                'showWatermarkText' => false,
            ],
            // call mPDF methods on the fly
            'methods' => [
                'SetHeader' => [$logo . ' ' . Yii::t('backend', 'Reporte de preparación de mercancías') . ' | | ' . Yii::t('backend', 'Fecha') . ': ' . GlobalFunctions::getCurrentDate('d/m/Y')],
                'SetTitle' => Yii::t('backend', 'Preparación de mercancía'),
                'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                'SetFooter' => ['|' . Yii::t('backend', 'Página') . ' {PAGENO}|'],
            ],
        ]);

        return $pdf->render();
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionClone($id, $noItem = 0)
    {
        $model = $this->findModel($id);
        $clone_model = new Invoice();
        $clone_model->attributes = $model->attributes;
        $clone_model->consecutive = $clone_model->generateConsecutive();
        $clone_model->status = UtilsConstants::PROFORMA_STATUS_STARTED;

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            //BEGIN payment method has purchase_order
            $payment_methods_assigned = PaymentMethodHasInvoice::getPaymentMethodByInvoiceId($id);

            $payment_methods_assigned_ids = [];
            foreach ($payment_methods_assigned as $value) {
                $payment_methods_assigned_ids[] = $value['payment_method_id'];
            }

            $clone_model->payment_methods = $payment_methods_assigned_ids;
            //END payment method has purchase_order



            //BEGIN seller has seller
            $seller_assigned = SellerHasInvoice::getSellerByInvoiceId($id);

            $seller_assigned_ids = [];
            foreach ($seller_assigned as $value) {
                $seller_assigned_ids[] = $value['seller_id'];
            }

            $clone_model->sellers = $seller_assigned_ids;
            //END seller method has seller


            //BEGIN collector has collector
            $collector_assigned = CollectorHasInvoice::getCollectorByInvoiceId($id);

            $collector_assigned_ids = [];
            foreach ($collector_assigned as $value) {
                $collector_assigned_ids[] = $value['collector_id'];
            }

            $clone_model->collectors = $collector_assigned_ids;
            //END seller method has collector   

            if ($clone_model->save()) {
                if ($noItem == 0) {
                    //clonar los items de la proforma y asociarlos a la nueva
                    $items_associates = ItemInvoice::findAll(['invoice_id' => $id]);

                    foreach ($items_associates as $index => $item) {
                        $new_item = new ItemInvoice();
                        $new_item->attributes = $item->attributes;
                        $new_item->invoice_id = $clone_model->id;
                        $new_item->save();
                    }
                }
                PaymentMethodHasInvoice::updateRelation($clone_model, [], 'payment_methods', 'payment_method_id');

                SellerHasInvoice::updateRelation($clone_model, [], 'sellers', 'seller_id');

                CollectorHasInvoice::updateRelation($clone_model, [], 'collectors', 'collector_id');

                $transaction->commit();

                GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Elemento clonado correctamente'));

                return $this->redirect(['update', 'id' => $clone_model->id]);
            } else {
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error clonando el elemento'));
            }
        } catch (Exception $e) {
            GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción clonando el elemento'));
            $transaction->rollBack();
        }

        return $this->redirect(['index']);
    }

    public function actionImportData()
    {
        $emisor = Issuer::find()->where(['id' => 1])->one();
        $ftpHost = $emisor->ftp_host;
        $ftpUser = $emisor->ftp_user;
        $ftpPass = $emisor->ftp_password;
        try {
            $ftpImportTask = new FtpImportTask($ftpHost, $ftpUser, $ftpPass);
            $ftpImportTask->run(true);
            Yii::$app->session->setFlash('success', 'Import completed successfully.');
        } catch (\Exception $e) {
            Yii::error('Error during FTP import: ' . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Error during import: ' . $e->getMessage());
        }

        Yii::$app->response->format = 'json';
        return [
            'result' => true
        ];
    }
}
