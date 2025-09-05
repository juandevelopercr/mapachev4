<?php

namespace backend\controllers;

use backend\components\ApiBCCR;
use backend\models\business\Invoice;
use backend\models\business\ItemInvoice;
use backend\models\business\ItemProforma;
use backend\models\business\ItemProformaSearch;
use backend\models\business\PaymentMethodHasInvoice;
use backend\models\business\PaymentMethodHasProforma;
use backend\models\business\CollectorHasInvoice;
use backend\models\business\SellerHasInvoice;
use backend\models\nomenclators\PaymentMethod;
use backend\models\business\Product;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use backend\models\settings\Setting;
use common\models\User;
use kartik\mpdf\Pdf;
use Mpdf\Output\Destination;
use Yii;
use backend\models\business\Proforma;
use backend\models\business\ProformaSearch;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\helpers\Url;
use yii\db\Exception;
use yii\web\Response;

/**
 * ProformaController implements the CRUD actions for Proforma model.
 */
class ProformaController extends Controller
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
     * Lists all Proforma models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProformaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Proforma model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModelItems = new ItemProformaSearch(['proforma_id' => $id]);
        $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);

        return $this->render('view', [
            'model' => $model,
            'dataProviderItems' => $dataProviderItems,
        ]);
    }

    /**
     * Creates a new Proforma model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Proforma();
        $model->loadDefaultValues();
        $model->scenario = 'create';
        $model->is_editable = 1;
        $model->consecutive = $model->generateConsecutive();
        $model->status = UtilsConstants::PROFORMA_STATUS_STARTED;
        $model->change_type = ApiBCCR::getChangeTypeOfIssuer();
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

        $model->request_date = date('Y-m-d');
        $model->branch_office_id = User::getBranchOfficeIdOfActiveUser();
        $model->box_id = User::getBoxIdOfActiveUser();

        if ($model->load(Yii::$app->request->post()))
        {
            $transaction = \Yii::$app->db->beginTransaction();

            try
            {
                if($model->save())
                {                    
                    PaymentMethodHasProforma::updateRelation($model,[],'payment_methods','payment_method_id');

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
     * Updates an existing Proforma model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if(isset($model) && !empty($model))
        {
            $model->scenario = 'update';
            $is_editable = (int) $model->is_editable;
            if(GlobalFunctions::getRol() !== User::ROLE_SUPERADMIN)
            {
                if($is_editable === 0)
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','La proforma solicitada no puede ser actualizada'));
                    return $this->redirect(['index']);
                }
            }

            $searchModelItems = new ItemProformaSearch();
            $searchModelItems->proforma_id = $model->id;
            $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);

            //BEGIN payment method has proforma
            $payment_methods_assigned = PaymentMethodHasProforma::getPaymentMethodByProformaId($id);

            $payment_methods_assigned_ids= [];
            foreach ($payment_methods_assigned as $value)
            {
                $payment_methods_assigned_ids[]= $value['payment_method_id'];
            }

            $model->payment_methods = $payment_methods_assigned_ids;
            //END payment method has proforma

            $old_status = (int)$model->status;

            if ($model->load(Yii::$app->request->post()))
            {
                $transaction = \Yii::$app->db->beginTransaction();

                try
                {
                    PaymentMethodHasProforma::updateRelation($model,$payment_methods_assigned,'payment_methods','payment_method_id');

                    if($model->save())
                    {
                        $new_status = (int) $model->status;
                        if($old_status !== $new_status && $new_status === UtilsConstants::PROFORMA_STATUS_APPROVED)
                        {
                            $model->verifyStock();
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
     * Deletes an existing Proforma model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

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

            return $this->redirect(['index']);
        }
        catch (Exception $e)
        {
            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción eliminando el elemento'));
            $transaction->rollBack();
        }
    }

    /**
     * Finds the Proforma model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Proforma the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Proforma::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend','La página solicitada no existe'));
        }
    }

    /**
    * Bulk Deletes for existing Proforma models.
    * If deletion is successful, the browser will be redirected to the 'index' page.
    * @return mixed
    */
    public function actionMultiple_delete()
    {
        if(Yii::$app->request->post('row_id'))
        {
            $transaction = \Yii::$app->db->beginTransaction();

            try
            {
                $pk = Yii::$app->request->post('row_id');
                $count_elements = count($pk);

                $deleteOK = true;
                $nameErrorDelete = '';
                $contNameErrorDelete = 0;

                foreach ($pk as $key => $value)
                {
                    $model= $this->findModel($value);

                    if(!$model->delete())
                    {
                        $deleteOK=false;
                        $nameErrorDelete= $nameErrorDelete.'['.$model->id.'] ';
                        $contNameErrorDelete++;
                    }
                }

                if($deleteOK)
                {
                    if($count_elements === 1)
                    {
                        GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento eliminado correctamente'));
                    }
                    else
                    {
                        GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elementos eliminados correctamente'));
                    }

                    $transaction->commit();
                }
                else
                {
                    if($count_elements === 1)
                    {
                        if($contNameErrorDelete===1)
                        {
                            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error eliminando el elemento').': <b>'.$nameErrorDelete.'</b>');
                        }
                    }
                    else
                    {
                        if($contNameErrorDelete===1)
                        {
                            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error eliminando el elemento').': <b>'.$nameErrorDelete.'</b>');
                        }
                        elseif($contNameErrorDelete>1)
                        {
                            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error eliminando los elementos').': <b>'.$nameErrorDelete.'</b>');
                        }
                    }
                }
            }
            catch (Exception $e)
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción eliminando el elemento'));
                $transaction->rollBack();
            }

            return $this->redirect(['index']);
        }
    }

    public function actionGetResumeProforma($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = Proforma::getResumeProforma($id);
        /*
        $proforma = Proforma::findOne($id);
        $percent_discount = (isset($proforma->discount_percent) && !empty($proforma->discount_percent))? $proforma->discount_percent : 0;
        if($percent_discount === 0)
        {
            $discount_proforma = 0;
        }
        else
        {
            $discount_proforma = $model->subtotal * ($percent_discount / 100);
        }

        //$total_price = $model->subtotal + $model->tax_amount - $discount_proforma;
        */
        //$total_price = $model->subtotal + $model->tax_amount - $model->exonerate_amount;

        return \Yii::$app->response->data = [
            'total_subtotal'=> GlobalFunctions::formatNumber($model->subtotal,2),
            'total_tax'=> GlobalFunctions::formatNumber($model->tax_amount,2),
            //'total_discount'=> GlobalFunctions::formatNumber($discount_proforma,2),
            'total_discount'=> GlobalFunctions::formatNumber($model->discount_amount,2),
            'total_exonerate'=> GlobalFunctions::formatNumber($model->exonerate_amount,2),
            'total_price'=> GlobalFunctions::formatNumber($model->price_total,2),
        ];
    }

    public function actionViewpdfcolonesoriginal($id)
    {
        return $this->Viewpdf($id, true);
    }

    public function actionViewpdfcolonescopia($id)
    {
        return $this->Viewpdf($id, false);
    }

    public function actionViewpdfdolaroriginal($id)
    {
        return $this->Viewpdf($id, true, $moneda = 'DOLAR');
    }

    public function actionViewpdfdolarcopia($id)
    {
        return $this->Viewpdf($id, false, $moneda = 'DOLAR');
    }

    public function Viewpdf($ids, $original, $moneda = 'COLONES', $destino = 'browser', $filename = 'Orden_Compra')
    {

        $logo = "<img src=\"".Setting::getUrlLogoBySettingAndType(1,Setting::SETTING_ID)."\" width=\"165\"/>";

        $configuracion = Setting::find()->where(['id'=>1])->one();
        $textCuentas = $configuracion->bank_information;        

        if (!is_array($ids) && !empty($ids))
            $ids = explode(',', $ids);

        $proformas = Proforma::find()->where(['id'=>$ids])->all();
        $data = '';
        foreach ($proformas as $proforma)
        {
            $items_proforma = ItemProforma::find()->where(['proforma_id'=>$proforma->id])->all();

            if (!empty($data))
                $data .= '<pagebreak>';

            $data .= $this->renderPartial('_pdf', [
                'proforma'=>$proforma,
                'items_proforma'=>$items_proforma,
                'logo'=>$logo,
                'moneda'=> $moneda,
                'original'=> $original,
                'textCuentas'=> $textCuentas,
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
                    'title' => 'Proforma',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => false,
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend','Proformas'),
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    'SetFooter' => ['|'.Yii::t('backend','Página').' {PAGENO}|'],
                ],
            ]);

            return $pdf->render();
        }
        else
        {
            if(!file_exists("uploads/proformas/") || !is_dir("uploads/proformas/")){
                try{
                    FileHelper::createDirectory("uploads/proformas/", 0777);
                }catch (\Exception $exception){
                    Yii::info("Error handling Proforma folder resources");
                }
            }

            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'destination' => Pdf::DEST_FILE,
                'content' => $data,
                'filename' => $filename,
                'options' => [
                    // any mpdf options you wish to set
                    'title' => 'Proforma',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => false,
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend','Proformas'),
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    'SetFooter' => ['|'.Yii::t('backend','Página').' {PAGENO}|'],
                ],
            ]);
            $pdf->render();

            return Url::to($filename);
        }
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionClone($id, $noItem = 0)
    {
        $model = $this->findModel($id);
        $clone_model = new Proforma();
        $clone_model->attributes = $model->attributes;
        $clone_model->consecutive = $clone_model->generateConsecutive();
        $clone_model->status = UtilsConstants::PROFORMA_STATUS_STARTED;
        $clone_model->facturada = NULL;

        $transaction = \Yii::$app->db->beginTransaction();

        try
        {
            //BEGIN payment method has purchase_order
            $payment_methods_assigned = PaymentMethodHasInvoice::getPaymentMethodByInvoiceId($id);

            $payment_methods_assigned_ids= [];
            foreach ($payment_methods_assigned as $value)
            {
                $payment_methods_assigned_ids[]= $value['payment_method_id'];
            }

            $clone_model->payment_methods = $payment_methods_assigned_ids;
            //END payment method has purchase_order


            if($clone_model->save())
            {
                if ($noItem == 0)
                {
                    //clonar los items de la proforma y asociarlos a la nueva
                    $items_associates = ItemProforma::findAll(['proforma_id' => $id]);

                    foreach ($items_associates AS $index => $item)
                    {
                        $new_item = new ItemProforma();
                        $new_item->attributes = $item->attributes;
                        $new_item->proforma_id = $clone_model->id;
                        $new_item->save();
                    }
                }

                //BEGIN payment method has proforma
                $payment_methods_assigned = PaymentMethodHasProforma::getPaymentMethodByProformaId($model->id);

                $payment_methods_assigned_ids= [];
                foreach ($payment_methods_assigned as $value)
                {
                    $payment_methods_assigned_ids[]= $value['payment_method_id'];
                }

                $clone_model->payment_methods = $payment_methods_assigned_ids;
                //END payment method has proforma

                PaymentMethodHasProforma::updateRelation($clone_model,[],'payment_methods','payment_method_id');                

                $transaction->commit();

                GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento clonado correctamente'));

                return $this->redirect(['update','id'=>$clone_model->id]);
            }
            else
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error clonando el elemento'));
            }
        }
        catch (Exception $e)
        {
            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción clonando el elemento'));
            $transaction->rollBack();
        }

        return $this->redirect(['index']);
    }

    /**
     * @param $type
     * @param $ids
     * @return Response
     */
    public function actionSend_pdf($type, $ids)
    {
        if (!is_array($ids) && !empty($ids))
            $ids = explode(',', $ids);

        $proformas = Proforma::find()
            ->where(['id'=>$ids])
            ->andWhere(['status' => UtilsConstants::PROFORMA_STATUS_APPROVED])
            ->all();

        $count_elements = count($proformas);
        $count_ids = count($ids);

        if($count_elements === 0)
        {
            GlobalFunctions::addFlashMessage('warning',Yii::t('backend','Las proformas seleccionadas deben estar APROBADAS para enviarlas'));
            return $this->redirect(['index']);
        }
        elseif($count_elements < $count_ids)
        {
            GlobalFunctions::addFlashMessage('warning',Yii::t('backend','Algunas proformas seleccionadas no se enviaron porque no están APROBADAS'));
        }

        $current_type = (int) $type;
        if($current_type === UtilsConstants::PDF_ORIGINAL_COLON_TYPE)
        {
            $is_original = true;
            $currency = 'COLONES';
        }
        elseif($current_type === UtilsConstants::PDF_COPY_COLON_TYPE)
        {
            $is_original = false;
            $currency = 'COLONES';
        }
        elseif($current_type === UtilsConstants::PDF_ORIGINAL_DOLLAR_TYPE)
        {
            $is_original = true;
            $currency = 'DOLAR';
        }
        elseif($current_type === UtilsConstants::PDF_COPY_DOLLAR_TYPE)
        {
            $is_original = false;
            $currency = 'DOLAR';
        }
        else
        {
            $is_original = true;
            $currency = 'COLONES';
        }

        $send_ok= true;
        $name_error_send = '';
        $count_name_error_send  = 0;

        foreach ($proformas AS $key => $model)
        {
            $file_name = 'uploads/proformas/proforma_'.$model->consecutive.'-'.time().'.pdf';
            $file_pdf = $this->Viewpdf($model->id, $is_original, $currency,'file',$file_name);

            $result_send = $model->sendEmail($file_pdf);
            if ($result_send == UtilsConstants::SEND_MAIL_RESPONSE_TYPE_EMPTY_EMAIL)
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('common','Ha ocurrido un error. El cliente no tiene una dirección de correo electrónica válida. Corrija la información e inténtelo nuevamente'));
                return $this->redirect(['index']);
            }
            elseif($result_send === UtilsConstants::SEND_MAIL_RESPONSE_TYPE_EXCEPTION)
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('common','Ha ocurrido un error. No se ha podido establecer la conexión con el servidor de correo'));
                return $this->redirect(['index']);
            }
            elseif($result_send === UtilsConstants::SEND_MAIL_RESPONSE_TYPE_ERROR)
            {
                $send_ok = false;
                $name_error_send= $name_error_send.'['.$model->consecutive.'] ';
                $count_name_error_send++;
            }
            elseif ($result_send === UtilsConstants::SEND_MAIL_RESPONSE_TYPE_SUCCESS)
            {
                $model->status = UtilsConstants::PROFORMA_STATUS_SENT;
                $model->save();
            }

            GlobalFunctions::deleteFile($file_name);
        }

        if($send_ok)
        {
            if($count_elements === 1)
            {
                GlobalFunctions::addFlashMessage('success',Yii::t('backend','Proforma enviada correctamente'));
            }
            else
            {
                GlobalFunctions::addFlashMessage('success',Yii::t('backend','Proformas enviadas correctamente'));
            }
        }
        else
        {
            if($count_elements === 1)
            {
                if($count_name_error_send===1)
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error enviando la proforma').': <b>'.$name_error_send.'</b>');
                }
            }
            else
            {
                if($count_name_error_send===1)
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error enviando la proforma').': <b>'.$name_error_send.'</b>');
                }
                elseif($count_name_error_send>1)
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error enviando las proformas').': <b>'.$name_error_send.'</b>');
                }
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * @param $id
     * @return Response
     */
    public function actionFacturar($id)
    {
        $model = $this->findModel($id);

        if ($model->status != UtilsConstants::PROFORMA_STATUS_APPROVED){
            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Solo las proformas con estado Aprobada pueden ser convertidas a factura. Cambie el estado e inténtelo nuevamente'));
            return $this->redirect(['index']); 
        }
        if (!is_null($model->facturada)){
            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','La proforma ya ha sido convertida a factura. Si desea puede clonarla e inténtarlo de nuevo'));
            return $this->redirect(['index']); 
        }
        
        $transaction = \Yii::$app->db->beginTransaction();

        try
        {
            $invoice = new Invoice();
            $invoice->attributes = $model->attributes;
            $invoice->status = UtilsConstants::INVOICE_STATUS_PENDING;
            $invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_NOT_SENT;
            $invoice->emission_date = date('Y-m-d H:i:s');
            $invoice->invoice_type = (isset($model->customer->pre_invoice_type) && !empty($model->customer->pre_invoice_type))? $model->customer->pre_invoice_type : UtilsConstants::PRE_INVOICE_TYPE_INVOICE;
            $invoice->consecutive = $invoice->generateConsecutive();

            //BEGIN payment method has proforma
            $payment_methods_assigned = PaymentMethodHasProforma::getPaymentMethodByProformaId($id);

            $payment_methods_assigned_ids= [];
            foreach ($payment_methods_assigned as $value)
            {
                $payment_methods_assigned_ids[]= $value['payment_method_id'];
            }

            $invoice->payment_methods = $payment_methods_assigned_ids;
            //END payment method has proforma

            $invoice->sellers = [$model->seller_id];
            $invoice->collectors = [$model->seller_id];
            //END seller method has seller

            if($invoice->save())
            {
                PaymentMethodHasInvoice::updateRelation($invoice,[],'payment_methods','payment_method_id');

                SellerHasInvoice::updateRelation($invoice, [], 'sellers', 'seller_id');

                CollectorHasInvoice::updateRelation($invoice, [], 'collectors', 'collector_id');

                //clonar los items de la proforma y asociarlos a la nueva factura
                $items_proforma_associated = ItemProforma::findAll(['proforma_id' => $id]);

                foreach ($items_proforma_associated AS $index => $item_proforma)
                {
                    $new_item_invoice = new ItemInvoice();
                    $new_item_invoice->attributes = $item_proforma->attributes;
                    $new_item_invoice->invoice_id = $invoice->id;
                    $new_item_invoice->save();
                }

                $model->facturada = 1;
                $model->save();
                $transaction->commit();

                $invoice->verifyStock();

                GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento creado correctamente'));

                return $this->redirect(['/invoice/update','id'=> $invoice->id]);
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

        return $this->redirect(['index']);
    }

}