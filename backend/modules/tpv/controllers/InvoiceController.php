<?php

namespace backend\modules\tpv\controllers;

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
use common\components\ApiV43\ApiAccess;
use backend\models\business\ItemInvoice;
use backend\models\business\CashRegister;
use backend\models\nomenclators\Currency;
use backend\models\business\InvoiceSearch;
use backend\models\nomenclators\BranchOffice;
use backend\models\business\ItemInvoiceSearch;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\PaymentMethod;
use common\components\ApiV43\ApiEnvioHacienda;
use backend\models\nomenclators\UtilsConstants;
use common\components\ApiV43\ApiFirmadoHacienda;
use common\components\ApiV43\ApiConsultaHacienda;
use backend\models\business\PaymentMethodHasInvoice;

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
        
       // $contenido = file_get_contents('https://mosaicvega.catinfog.com/tpv/ajax.php');
       
        //$fp = fopen("myText.txt","wb");
        //fwrite($fp,$contenido);
        //fclose($fp);

        //die(var_dump("...."));
        $customer = Customer::find()->where(['code' => '000001'])->one();
        //$searchModel = new InvoiceSearch();
        //$is_point_sale = 1;
        //$searchModel = new InvoiceSearch();
        //$dataProvider = $searchModel->search(Yii::$app->request->queryParams, $is_point_sale);
        $this->view->params['customer'] = $customer;
        return $this->render('index', [
            'customer'=>$customer,
            //'searchModel' => $searchModel,
            //'dataProvider' => $dataProvider,
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
        $dataProviderItems = $searchModelItems->searchTPV(Yii::$app->request->queryParams);

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
        foreach ($data as $key => $value){
            $defaulPayment[] = $key;
        } 
        $model->payment_methods = $defaulPayment;

        $currency = Currency::findOne(['symbol' => 'CRC']);
        if($currency !== null)
        {
            $model->currency_id = $currency->id;
        }

        $model->emission_date = date('Y-m-d H:i:s');
        $model->branch_office_id = User::getBranchOfficeIdOfActiveUser();
        $model->box_id = User::getBoxIdOfActiveUser();

        // Chequear que el usuario activo tenga rol agente y que la caja esté abierta
        if (GlobalFunctions::getRol() != User::ROLE_AGENT) {
            GlobalFunctions::addFlashMessage('warning',Yii::t('backend',"Usted no tiene acceso a crear facturas del punto de venta, solo usuarios con rol agente pueden realizarlas"));
            return $this->redirect(['index']);      
        }
        if (!CashRegister::cajaAbierta($model->box_id)){
            $box_name = $model->box->numero.'-'.$model->box->name;
            GlobalFunctions::addFlashMessage('warning',Yii::t('backend',"La caja {box_name}, no se ha abierto. Para poder crear facturas debe abrir antes la caja", ['box_name'=>$box_name]));
            return $this->redirect(['index']);      
        }

        $model->invoice_type = UtilsConstants::PRE_INVOICE_TYPE_TICKET;
        $customer = Customer::find()->where(['code' => '000001'])->one();
        if ($customer !== null)
        {
            $model->customer_id = $customer->id;        
            //$model->seller_id = (isset($customer->seller_id) && !empty($customer->seller_id))? $customer->seller_id : null;
            //$model->collector_id = (isset($customer->collector_id) && !empty($customer->collector_id))? $customer->collector_id : null;
            $model->condition_sale_id = (isset($customer->condition_sale_id) && !empty($customer->condition_sale_id))? $customer->condition_sale_id : null;
            $model->credit_days_id = (isset($customer->credit_days_id) && !empty($customer->credit_days_id))? $customer->credit_days_id : null;
        }

        $model->seller_id = yii::$app->user->id;
        $model->collector_id = yii::$app->user->id;

        if ($model->load(Yii::$app->request->post()))
        {
            $model->emission_date = date('Y-m-d H:i:s');
            if (is_null($model->box_id) || empty($model->box_id)) {
                $model->addError('box_id',Yii::t('backend','Debe seleccionar el punto de venta'));

                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Debe seleccionar el punto de venta'));

                return $this->render('create', [
                    'model' => $model,
                ]);
            }                

            $model->consecutive = $model->generateConsecutive();

            $transaction = \Yii::$app->db->beginTransaction();

            try
            {
                if(Invoice::find()->select(['consecutive'])->where(['consecutive' => $model->consecutive])->exists())
                {
                    $model->consecutive = $model->generateConsecutive();
                }

                $model->status_account_receivable_id = UtilsConstants::HACIENDA_STATUS_PENDING; // Para la gestión de cuentas por cobrar
                if ($model->condition_sale_id !== ConditionSale::getIdCreditConditionSale())
                    $model->pay_date = date('Y-m-d');

                $errors = 0;
                if ($model->contingency)
                {
                    $model->reference_code = '05'; //Sustituye comprobante provisional por $this->contingency
                    if (is_null($model->reference_number) || empty($model->reference_number))
                    {
                        $model->addError('reference_number',Yii::t('backend','Debe definir el número de referencia en los datos de contingencia'));
                        $errors++;
                    }
                    elseif (is_null($model->reference_emission_date) || empty($model->reference_emission_date))
                    {
                        $model->addError('reference_emission_date',Yii::t('backend','Debe definir la fecha de emisión de referencia en los datos de contingencia'));
                        $errors++;
                    }
                    elseif (is_null($model->reference_reason) || empty($model->reference_reason))
                    {
                        $model->addError('reference_emission_date',Yii::t('backend','Debe definir la razón de la contingencia'));
                        $errors++;
                    }                    
                }                

                if($errors > 0)
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error creando el elemento'));

                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }

                if($model->save())
                {
                    PaymentMethodHasInvoice::updateRelation($model,[],'payment_methods','payment_method_id');

                    $transaction->commit();

                    GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento creado correctamente'));

                    return $this->redirect(['update','id'=>$model->id]);
                }
                else
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error creando el elemento'));
                }
            }
            catch (Exception $e)
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción creando el elemento'));
                $transaction->rollBack();
            }
        }

        return $this->render('create', [
            'model' => $model,
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

        if($status_hacienda !== UtilsConstants::HACIENDA_STATUS_NOT_SENT){
            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','No es posible actualizar una factura enviada a hacienda'));
            return $this->redirect(['index']);
        }

        if(isset($model) && !empty($model))
        {
            $searchModelItems = new ItemInvoiceSearch();
            $searchModelItems->invoice_id = $model->id;
            $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);
            if(is_null($model->status_hacienda))
            {
                $model->status_hacienda = UtilsConstants::HACIENDA_STATUS_NOT_SENT;
            }

            //BEGIN payment method has invoice
            $payment_methods_assigned = PaymentMethodHasInvoice::getPaymentMethodByInvoiceId($id);

            $payment_methods_assigned_ids= [];
            foreach ($payment_methods_assigned as $value)
            {
                $payment_methods_assigned_ids[]= $value['payment_method_id'];
            }

            $model->payment_methods = $payment_methods_assigned_ids;
            //END payment method has invoice

            $old_status = (int)$model->status;

            if ($model->load(Yii::$app->request->post()))
            {
                $transaction = \Yii::$app->db->beginTransaction();
                $total_items = ItemInvoice::find()->where(['invoice_id' => $id])->count();
                $ready_to_send = (int) $model->ready_to_send_email;

                if($ready_to_send === 1 && $total_items === 0)
                {
                    $model->ready_to_send_email = 0;
                    $model->addError('ready_to_send_email', 'No es posible marcar como "Lista para enviar" una factura sin items');
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','No es posible marcar como "Lista para enviar" una factura sin items'));

                    return $this->render('update', [
                        'model' => $model,
                        'searchModelItems' => $searchModelItems,
                        'dataProviderItems' => $dataProviderItems,
                    ]);
                }

                try
                {
                    PaymentMethodHasInvoice::updateRelation($model,$payment_methods_assigned,'payment_methods','payment_method_id');

                    if($model->save())
                    {
                        $new_status = (int) $model->status;
                        if($old_status !== $new_status)
                        {
                            $model->verifyStock();
                        }

                        //Enviar factura por correo si aplica
                        if($old_email_sent === 0 && $old_ready_to_send === 0 && $old_ready_to_send !== $ready_to_send)
                        {
                            $email_model = new EnviarEmailForm();
                            $issuer = Issuer::find()->one();
                            $email_model->id = $model->id;
                            $email_model->de = $issuer->email;
                            $email_model->para = $model->customer->email;
                            $email_model->nombrearchivo = $model->key.'.pdf';
                            $email_model->asunto = 'Envío de Factura Electrónica';
                            $response = $this->enviareamil($email_model, $model);

                            $model->email_sent = 1;
                            $model->save();

                        }

                        $transaction->commit();

                        GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento actualizado correctamente'));

                        return $this->redirect(['index']);
                    }
                    else
                    {
                        GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error actualizando el elemento'));
                    }
                }
                catch (Exception $e)
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción actualizando el elemento'));
                    $transaction->rollBack();
                }
            }
        }
        else
        {
            GlobalFunctions::addFlashMessage('warning',Yii::t('backend','El elemento buscado no existe'));
        }

        return $this->render('update', [
            'model' => $model,
            'searchModelItems' => $searchModelItems,
            'dataProviderItems' => $dataProviderItems,
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

        if($status_hacienda === UtilsConstants::HACIENDA_STATUS_NOT_SENT)
        {
            $transaction = \Yii::$app->db->beginTransaction();

            try
            {
                if($model->delete())
                {
                    $transaction->commit();

                    GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento eliminado correctamente'));
                }
                else
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error eliminando el elemento'));
                }
            }
            catch (Exception $e)
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción eliminando el elemento'));
                $transaction->rollBack();
            }
        }
        else
        {
            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','No se puede eliminar una factura enviada a Hacienda'));
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
        if (($model = Invoice::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend','La página solicitada no existe'));
        }
    }

    public function actionGetResumeInvoice($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = Invoice::getResumeInvoice($id);

        return \Yii::$app->response->data = [
            'total_subtotal'=> GlobalFunctions::formatNumber($model->subtotal,2),
            'total_tax'=> GlobalFunctions::formatNumber($model->tax_amount,2),
            'total_discount'=> GlobalFunctions::formatNumber($model->discount_amount,2),
            'total_exonerate'=> GlobalFunctions::formatNumber($model->exonerate_amount,2),
            'total_price'=> GlobalFunctions::formatNumber($model->price_total,2),
        ];
    }

    public function showPdf($ids, $original, $moneda = 'COLONES', $destino = 'browser', $filename = 'Factura')
    {
        $logo = "<img src=\"".Setting::getUrlLogoBySettingAndType(1,Setting::SETTING_ID)."\" width=\"165\"/>";
        if (!is_array($ids) && !empty($ids))
            $ids = explode(',', $ids);

        $invoices = Invoice::find()->where(['id'=>$ids])->all();
        $data = '';
        foreach ($invoices as $invoice)
        {
            $qr_code_invoice = $invoice->generateQrCode();
            $img_qr = '<img src="'.$qr_code_invoice.'" width="100"/>';

            $items_invoice = ItemInvoice::find()->where(['invoice_id'=>$invoice->id])->all();

            if (!empty($data))
                $data .= '<pagebreak>';

            $data .= $this->renderPartial('_pdf', [
                'invoice' => $invoice,
                'items_invoice' => $items_invoice,
                'logo' => $logo,
                'moneda' => $moneda,
                'original' => $original,
                'img_qr' => $img_qr
            ]);
        }

        if ($destino == 'browser')
        {
            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'destination' => Pdf::DEST_BROWSER,                
                'content' => $data,
                'filename' => $filename,
                'options' => [
                    // any mpdf options you wish to set
                    'title' => 'Factura',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => false,
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend','Facturas'),
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    'SetFooter' => ['|'.Yii::t('backend','Página').' {PAGENO}|'],
                ],
            ]);

            return $pdf->render();
        }
        else
        {
            if(!file_exists("uploads/invoice/") || !is_dir("uploads/invoice/")){
                try{
                    FileHelper::createDirectory("uploads/invoice/", 0777);
                }catch (\Exception $exception){
                    Yii::info("Error handling Factura folder resources");
                }
            }

            $file_pdf_save = Yii::getAlias('@backend').'/web/uploads/invoice/'.$filename;

            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'destination' => Pdf::DEST_FILE,
                'content' => $data,
                'filename' => $file_pdf_save,
                'options' => [
                    // any mpdf options you wish to set
                    'title' => 'Factura',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => false,
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend','Facturas'),
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    'SetFooter' => ['|'.Yii::t('backend','Página').' {PAGENO}|'],
                ],
            ]);
            $pdf->render();

            return $file_pdf_save;
        }
    }

    public function showTicketPdf($ids, $original, $moneda = 'COLONES', $destino = 'browser', $filename = 'Tiquete',$show_logo = true)
    {
        if($show_logo)
        {
            $logo = "<img src=\"".Setting::getUrlLogoBySettingAndType(1,Setting::SETTING_ID)."\" width=\"100\"/>";
        }
        else
        {
            $logo = '';
        }

        if (!is_array($ids) && !empty($ids))
            $ids = explode(',', $ids);

        $invoices = Invoice::find()->where(['id'=>$ids])->all();
        $data = '';
        if($show_logo)
        {
            $pivot = $max_dynamic_height = 250;
        }
        else
        {
            $pivot = $max_dynamic_height = 235;
        }

        foreach ($invoices as $invoice)
        {
            $dynamic_height = $pivot;

            $qr_code_invoice = $invoice->generateQrCode();
            $img_qr = "<img src=\"".$qr_code_invoice."\"/>";

            $items_invoice = ItemInvoice::find()->where(['invoice_id'=>$invoice->id])->all();
            $total_items = count($items_invoice);
            $dynamic_height += ($total_items*8);
            if($dynamic_height > $max_dynamic_height)
            {
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
                'img_qr' => $img_qr
            ]);
            
            $data .= $data;
        }

        if ($destino == 'browser')
        {
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
                    'showWatermarkText' => false,
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend','Tiquetes'),
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                ],
            ]);

            $pdf->format = [76.2,$max_dynamic_height];
            $pdf->marginLeft = 2.5;
            $pdf->marginRight = 2.5;
            $pdf->marginTop = 5;
            $pdf->marginBottom = 0;
            $pdf->marginHeader = 0;
            $pdf->marginFooter = 0;
            $pdf->defaultFontSize = 13;

            return $pdf->render();
        }
        else
        {
            if(!file_exists("uploads/invoice/") || !is_dir("uploads/invoice/")){
                try{
                    FileHelper::createDirectory("uploads/invoice/", 0777);
                }catch (\Exception $exception){
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
                    'showWatermarkText' => false,
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend','Facturas'),
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    'SetFooter' => ['|'.Yii::t('backend','Página').' {PAGENO}|'],
                ],
            ]);

            $pdf->format = [76.2,$max_dynamic_height];
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
        if($type === UtilsConstants::PRE_INVOICE_TYPE_INVOICE)
        {
           // die(var_dump("1"));
            return $this->showPdf($id,true);
        }
        else
        {
            //die(var_dump("2"));
            return $this->showTicketPdf($id,true);
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
        $invoice = Invoice::find()->where(['id'=>$id])->one();
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
        $headers->add('Content-Disposition', 'attachment; filename='.'FE-'.$invoice->key.".xml");
        return $xml;
    }

    public function actionViewpdfcontingencia($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;

        $logo = "<img src=\"".Setting::getUrlLogoBySettingAndType(1,Setting::SETTING_ID)."\" width=\"100\"/>";

        $data = $this->renderPartial('_pdf_contingencia', [
            'logo'=>$logo,
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
                'SetTitle' => Yii::t('backend','PDF_Contingencia'),
                'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                'SetFooter' => ['|'.Yii::t('backend','Página').' {PAGENO}|'],
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
        if ($model->load(Yii::$app->request->post()))
        {
            $model->id = Yii::$app->request->post()['EnviarEmailForm']['id'];

            $factura = Invoice::find()->where(['id'=>$id])->one();
            $model->nombrearchivo = '';
            $model->nombrearchivo .= $factura->key.'.pdf';
            $model->cc = Yii::$app->request->post()['EnviarEmailForm']['cc'];
            $model->cuerpo = Yii::$app->request->post()['EnviarEmailForm']['cuerpo'];

            $respuesta = $this->enviareamil($model, $factura);

            Yii::$app->response->format = 'json';
            if ($respuesta)
            {
                $factura->ready_to_send_email = 1;
                $factura->email_sent = 1;
                $factura->save(false);

                $msg .= 'Se ha enviado las factura por correo electrónico';
                $type = 'success';
            }
            else
            {
                $msg .= 'Ha ocurrido un error. No se ha podido enviar el correo electrónico';
                $type = 'danger';
            }

            return \Yii::$app->response->data  = [
                'message' => $msg,
                'type'=> $type,
                'titulo'=>"Informaci&oacute;n <hr class=\"kv-alert-separator\">",
            ];
        }
        else
        {
            $factura = Invoice::find()->where(['id'=>$id])->one();
            $emisor = Issuer::find()->one();
            $model->de = $emisor->email;
            $model->para = $factura->customer->email;

            $model->nombrearchivo = $factura->key.'.pdf';
            $model->asunto = 'Envío de Factura Electrónica';
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
        }
        else
        {
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
						<span style=\"text-align:center;color:#3157F2;font-size:20px;\">Envío de factura Electrónica</span><br />
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
										<strong>Clave Numérica: ".$factura->key."</strong>
									</td>
								</tr>
								<tr>
									<th style=\"background-color:#CCC; text-align:left\">Emisor</th>
									<td>".$emisor->name."</td>
								</tr>
								<tr>
									<th style=\"background-color:#CCC; text-align:left\">Receptor</th>
									<td>".$factura->customer->name."</td>
								</tr>     
								<tr>
									<th style=\"background-color:#CCC; text-align:left\">No. Documento</th>
									<td>".$factura->consecutive."</td>
								</tr>                          
								<tr>
									<th style=\"background-color:#CCC; text-align:left\">Fecha</th>
									<td>".date('d-m-Y', strtotime($factura->emission_date))."</td>
								</tr>                          
								<tr>
									<th style=\"background-color:#CCC; text-align:left\">Importe</th>
									<td>".$factura->currency->symbol." ".number_format($factura->getTotalAmount(), 2, ".", ",")."</td>
								</tr>                          
							</thead>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						".$model->cuerpo."
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


            $mensage = Yii::$app->mail->compose("layouts/html", ['content'=>$model->cuerpo])
                ->setTo($to)
                ->setFrom($model->de)
                ->setCc($arr_cc)
                ->setSubject($model->asunto)
                ->setTextBody($body_html)
                ->setHtmlBody($body_html);

            // Adjuntar PDF
            $nombrearchivo = 'FE-'.$factura->key.'.pdf';
            $archivo = '';
            $listid[] = $factura->id;
            $archivo = $this->showPdf($listid, true, 'COLONES','file', $nombrearchivo);
            if (!empty($archivo)) {
                $mensage->attach($archivo, ['fileName'=>$nombrearchivo]);
            }


            // Adjuntar XML del SISTEMA
            $invoice = Invoice::find()->where(['id'=>$factura->id])->one();
            $items_invoice = ItemInvoice::find()->where(['invoice_id' => $factura->id])->all();

            $apiXML = new ApiXML();
            $issuer = Issuer::find()->one();
            $xml = $apiXML->genXMLFe($issuer, $invoice, $items_invoice);

            $p12Url = $issuer->getFilePath();
            $pinP12 = $issuer->certificate_pin;

            $doc_type = '01'; // Factura
            $apiFirma = new ApiFirmadoHacienda();
            $xmlFirmado = $apiFirma->firmar($p12Url, $pinP12, $xml, $doc_type);

            $xml = base64_decode($xmlFirmado);

            $nombre_archivo = 'FE-'.$factura->key.'.xml';
            // create attachment on-the-fly
            $mensage->attachContent($xml, ['fileName' => $nombre_archivo, 'contentType' => 'text/plain']);


            // Adjuntar XML de respuesta de Hacienda si existe
            $url_xml_hacienda_verificar = Yii::getAlias('@backend/web/uploads/xmlh/FE-MH-'.$factura->key.'.xml');
            $nombre_archivo = 'FE-MH'.$factura->key.'.xml';
            if (file_exists($url_xml_hacienda_verificar))
                $mensage->attach($url_xml_hacienda_verificar, ['fileName' => $nombre_archivo]);


            if ($mensage->send())
                $respuesta = true;
            else
                $respuesta = false;

        }
        return $respuesta;
    }

    public function actionEnviarFacturaHacienda($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $factura = Invoice::findOne($id);

        $datos = $this->validaDatosFactura($factura);
        $emisor = Issuer::find()->one();
        $error = $datos['error'];
        if ($error == 0)
        {
            // Si todas las validaciones son correctas, proceder al proceso
            // Logearse en la api y obtener el token;
            $apiAccess = new ApiAccess();
            $datos = $apiAccess->loginHacienda($emisor);

            $error = $datos['error'];
            if ($error == 0){
                // Obtener el xml firmado electrónicamente
                $invoice = Invoice::find()->where(['id'=>$id])->one();
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

                $respuesta = $datos['response'];
                $code = $respuesta->getHeaders()->get('http-code');
                if ($code == '202' || $code == '201' || $code == '200')
                {
                    $mensaje = "La factura electrónica con clave: [".$factura->key."] se recibió correctamente, queda pendiente la validación de esta y el envío de la respuesta de parte de Hacienda.";
                    $factura->status_hacienda = UtilsConstants::HACIENDA_STATUS_RECEIVED; // Recibido
                    $factura->save(false);
                    $type = 'success';
                    $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
                }
                elseif ($code == '400')
                {
                        $error = 1;
                        $mensaje = utf8_encode($respuesta->getHeaders()->get('X-Error-Cause'));
                        $type = 'danger';
                        $titulo = "Error <hr class=\"kv-alert-separator\">";
                }
                else
                {
                    $error = 1;
                    $mensaje = "Ha ocurrido un error desconocido al enviar la factura electrónica con clave: [".$factura->key."]. Póngase en contacto con el administrador del sistema";
                    $type = 'danger';
                    $titulo = "Error <hr class=\"kv-alert-separator\">";
                }
            }
            else
            {
                $error = 1;
                $mensaje = $datos['mensaje'];
                $type = 'danger';
                $titulo = "Error <hr class=\"kv-alert-separator\">";
            }
            $apiAccess->CloseSesion($apiAccess->token, $emisor);
        }
        else
        {
            $mensaje = $datos['mensaje'];
            $type = $datos['type'];
            $titulo = $datos['titulo'];
        }
        return ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
    }

    public function validaDatosFactura($factura)
    {
        // Valida que los datos de la factura, que tenga detalle y emisor definido
        $error = 0;
        $mensaje = '';
        $type = '';
        $titulo	= '';
        if (is_null($factura))
        {
            $error = 1;
            $mensaje = 'La factura seleccionada no se encuentra en la base de datos';
            $type = 'danger';
            $titulo = "Error <hr class=\"kv-alert-separator\">";
        }

        $items_exists = ItemInvoice::find()->where(['invoice_id' => $factura->id])->exists();
        if (!$items_exists)
        {
            $error = 1;
            $mensaje = 'La factura seleccionada no contiene ninguna línea de producto / servicio. Por favor revise la información e inténtelo nuevamente';
            $type = 'warning';
            $titulo = "Advertencia <hr class=\"kv-alert-separator\">";
        }


        $configuracion = Issuer::find()->one();
        if (is_null($configuracion))
        {
            $error = 1;
            $mensaje = 'No se ha podido obtener la información del emisor de la factura. Por favor revise los datos e inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema';
            $type = 'danger';
            $titulo = "Error <hr class=\"kv-alert-separator\">";
        }
        return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
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
        if($status_hacienda === UtilsConstants::HACIENDA_STATUS_NOT_SENT || $status_hacienda === UtilsConstants::HACIENDA_STATUS_RECEIVED)
        {
            if ($error == 0 && $proceder == true)
            {
                // Si todas las validaciones son correctas, proceder al proceso
                // Logearse en la api y obtener el token;
                $apiAccess = new ApiAccess();
                $datos = $apiAccess->loginHacienda($emisor);
                $error = $datos['error'];
                if ($error == 0)
                {
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
                        // Extraer del inventario 
                        $invoice = Invoice::findOne($invoice_id);
                        $items_associates = ItemInvoice::findAll(['invoice_id' => $invoice_id]);
                        foreach ($items_associates AS $index => $item)
                        {  
                            if(isset($item->product_id) && !empty($item->product_id))
                            {
                                if ($invoice->invoice_type == UtilsConstants::PRE_INVOICE_TYPE_INVOICE)
                                    $tipo = 'de la Factura Electrónica';
                                else
                                    $tipo = 'del Tiquete Electrónico';

                                $observations = 'Salida por aceptación en hacienda ' . $tipo . ' # ' . $invoice->consecutive;
                                $adjustment_type = UtilsConstants::ADJUSTMENT_TYPE_INVOICE_SALES;
                                    
                                // Chequear si ya se ha realizado ese ajuste
                                $adjustment = Adjustment::find()->where(['product_id'=>$item->product_id, 'key'=> $invoice->key, 'type'=>$adjustment_type, 
                                                            'origin_branch_office_id'=>$invoice->branch_office_id, 'observations'=>$observations])->one();

                                if (is_null($adjustment)){
                                    // Se calcula la cantidad a extraer según la unidad de medidad del producto
                                    $quantity = Product::getUnitQuantityByItem($item->product_id, $item->quantity, $item->unit_type_id);

                                    Product::extractInventory($item->product_id, $adjustment_type, $invoice->branch_office_id, $quantity, $invoice->id, $observations, $invoice->key);
                                }
                            }
                        }
                    }
                }
                else
                {
                    $mensaje = 'No se ha podido autenticar en la api de hacienda. Inténtelo nuevamente';
                    $type = 'danger' ;
                    $titulo = "Error <hr class=\"kv-alert-separator\">";
                }
            }
        }
        else
        {
            $mensaje = 'El estado de hacienda ya fue recibido';
            $type = 'warning';
            $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
            $actualizar = 0;
        }

        return \Yii::$app->response->data  =  ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'actualizar'=>$actualizar];
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
        $model->reference_emission_date = date('Y-m-d H:i:s');
        $model->reference_code = '01';
        $model->reference_reason = 'Anula documento de referencia';
        $model->consecutive = $model->generateConsecutive();
        $model->key = $model->generateKey();

        //BEGIN payment method has invoice
        $payment_methods_assigned = PaymentMethodHasInvoice::getPaymentMethodByInvoiceId($id);

        $payment_methods_assigned_ids= [];
        foreach ($payment_methods_assigned as $value)
        {
            $payment_methods_assigned_ids[]= $value['payment_method_id'];
        }

        $model->payment_methods = $payment_methods_assigned_ids;
        //END payment method has invoice

        $searchModelItems = new ItemInvoiceSearch();
        $searchModelItems->invoice_id = $model->id;
        $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);

        if ($model->load(Yii::$app->request->post()))
        {
            $transaction = \Yii::$app->db->beginTransaction();

            try
            {
                $errors = 0;
                if (empty($model->reference_emission_date))
                {
                    $model->addError('reference_emission_date','Debe especificar la fecha de emisión');
                    $errors++;
                }

                if (empty($model->reference_reason))
                {
                    $model->addError('reference_reason','Debe especificar la razón por la cual se corrige la factura');
                    $errors++;
                }

                if($errors > 0)
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error actualizando el elemento'));

                    return $this->render('correction', [
                        'model' => $model,
                        'searchModelItems' => $searchModelItems,
                        'dataProviderItems' => $dataProviderItems,
                    ]);
                }

                if(Invoice::find()->select(['consecutive'])->where(['consecutive' => $model->consecutive])->exists())
                {
                    $model->consecutive = $model->generateConsecutive();
                }

                if($model->save())
                {
                    PaymentMethodHasInvoice::updateRelation($model,[],'payment_methods','payment_method_id');

                    //clonar los items de la proforma y asociarlos a la nueva
                    $items_associates = ItemInvoice::findAll(['invoice_id' => $id]);

                    foreach ($items_associates AS $index => $item)
                    {
                        $new_item = new ItemInvoice();
                        $new_item->attributes = $item->attributes;
                        $new_item->invoice_id = $model->id;
                        $new_item->save();
                    }

                    $factura->status_hacienda = UtilsConstants::HACIENDA_STATUS_ANULATE;
                    $factura->save(false);

                    $transaction->commit();

                    GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento actualizado correctamente'));

                    return $this->redirect(['update', 'id' => $model->id]);
                }
                else
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error actualizando el elemento'));
                }
            }
            catch (\Exception $e)
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción actualizando el elemento'));
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
        $logo = "<img src=\"".Setting::getUrlLogoBySettingAndType(1,Setting::SETTING_ID)."\" width=\"130\"/>";

        if (!is_array($ids) && !empty($ids))
            $ids = explode(',', $ids);

        $invoices = Invoice::find()->where(['id'=>$ids])->all();
        $data = '';
        foreach ($invoices as $invoice)
        {
            $qr_code_invoice = $invoice->generateQrCode();
            $img_qr = '<img src="'.$qr_code_invoice.'" width="100"/>';

            $items_invoice = ItemInvoice::find()->where(['invoice_id'=>$invoice->id])->all();

            if (!empty($data))
                $data .= '<pagebreak>';

            $data .= $this->renderPartial('_print_pdf', [
                'invoice' => $invoice,
                'items_invoice' => $items_invoice,
                'logo' => $logo,
                'moneda' => "COLONES",
                'original' => true,
                'img_qr' => $img_qr
            ]);

            $data .= '<pagebreak>';

            $data .= $this->renderPartial('_print_pdf', [
                'invoice' => $invoice,
                'items_invoice' => $items_invoice,
                'logo' => $logo,
                'moneda' => "COLONES",
                'original' => false,
                'img_qr' => $img_qr
            ]);
        }


        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE,
            //'orientation' => Pdf::ORIENT_PORTRAIT, //ORIENT_LANDSCAPE, // ORIENT_PORTRAIT
            'orientation' => Pdf::ORIENT_PORTRAIT, // ORIENT_PORTRAIT
            'destination' => Pdf::DEST_BROWSER,
            //'format' => [216, 280], // Legal page size in mm
            //'format' => [108, 280], // Legal page size in mm
            'format' => [216, 140],
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
                'SetTitle' => Yii::t('backend','Factura'),
                'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                //'SetFooter' => ['|'.Yii::t('backend','Página').' {PAGENO}|'],
            ],
        ]);

        $pdf->marginTop = 4;
        $pdf->marginLeft = 4;
        $pdf->marginRight = 4;
        $pdf->marginBottom = 0;
        $pdf->marginHeader = 0;
        $pdf->marginFooter = 0;
        $pdf->defaultFontSize = 10;

        return $pdf->render();
    }

    public function actionPrintpdf($id)
    {
        return $this->printPdf($id);
    }

}
