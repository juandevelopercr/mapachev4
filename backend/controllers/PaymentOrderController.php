<?php

namespace backend\controllers;

use backend\components\ApiBCCR;
use backend\models\business\AttachPoSearch;
use backend\models\business\ItemPaymentOrder;
use backend\models\business\ItemPaymentOrderSearch;
use backend\models\business\PaymentMethodHasPaymentOrder;
use backend\models\business\ReceptionItemPo;
use backend\models\business\ReceptionItemPoSearch;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use backend\models\settings\Setting;
use common\models\User;
use yii\helpers\Url;
use Yii;
use backend\models\business\PaymentOrder;
use backend\models\business\PaymentOrderSearch;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\db\Exception;
use kartik\mpdf\Pdf;

/**
 * PaymentOrderController implements the CRUD actions for PaymentOrder model.
 */
class PaymentOrderController extends Controller
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
     * Lists all PaymentOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PaymentOrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PaymentOrder model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModelItems = new ItemPaymentOrderSearch(['payment_order_id' => $id]);
        $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);

        return $this->render('view', [
            'model' => $model,
            'dataProviderItems' => $dataProviderItems,
        ]);
    }

    /**
     * Creates a new PaymentOrder model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PaymentOrder();
        $model->loadDefaultValues();
        $model->number = $model->generateOrderNumber();
        $model->status_payment_order_id = UtilsConstants::PAYMENT_ORDER_STATUS_TO_APPROVAL;
        $model->payout_status = UtilsConstants::PAYOUT_STATUS_PENDING;
        $model->is_editable = 1;
        $model->change_type = ApiBCCR::getChangeTypeOfIssuer();
        $currency = Currency::findOne(['symbol' => 'CRC']);
        if($currency !== null)
        {
            $model->currency_id = $currency->id;
        }
        $model->request_date = date('Y-m-d');
        $model->require_date = date('Y-m-d', strtotime('+5 day ' . date('Y-m-d')));

        if ($model->load(Yii::$app->request->post()))
        {
            $transaction = \Yii::$app->db->beginTransaction();

            try
            {
                if(PaymentOrder::find()->select(['number'])->where(['number' => $model->number])->exists())
                {
                    $model->number = $model->generateOrderNumber();
                }

                if($model->save())
                {
                    PaymentMethodHasPaymentOrder::updateRelation($model,[],'payment_methods','payment_method_id');

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
     * Updates an existing PaymentOrder model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if(isset($model) && !empty($model))
        {
            $is_editable = (int) $model->is_editable;
            if(GlobalFunctions::getRol() !== User::ROLE_SUPERADMIN)
            {
                if($is_editable === 0)
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','La orden de compra solicitada no puede ser actualizada'));
                    return $this->redirect(['index']);
                }
            }

            $searchModelItems = new ItemPaymentOrderSearch();
            $searchModelItems->payment_order_id = $model->id;
            $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);

            $searchModelReceptionItems = new ReceptionItemPoSearch();
            $searchModelReceptionItems->payment_order_id = $model->id;
            $dataProviderReceptionItems = $searchModelReceptionItems->search(Yii::$app->request->queryParams);

            $searchModelAttachs = new AttachPoSearch();
            $searchModelAttachs->payment_order_id = $model->id;
            $dataProviderAttachs = $searchModelAttachs->search(Yii::$app->request->queryParams);

            //BEGIN payment method has payment order
            $payment_methods_assigned = PaymentMethodHasPaymentOrder::getPaymentMethodByPaymentOrderId($id);

            $payment_methods_assigned_ids= [];
            foreach ($payment_methods_assigned as $value)
            {
                $payment_methods_assigned_ids[]= $value['payment_method_id'];
            }

            $model->payment_methods = $payment_methods_assigned_ids;
            //END payment method has payment order

            if ($model->load(Yii::$app->request->post()))
            {
                $transaction = \Yii::$app->db->beginTransaction();

                try
                {
                    PaymentMethodHasPaymentOrder::updateRelation($model,$payment_methods_assigned,'payment_methods','payment_method_id');

                    if($model->save())
                    {
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
            'searchModelReceptionItems' => $searchModelReceptionItems,
            'dataProviderReceptionItems' => $dataProviderReceptionItems,
            'searchModelAttachs' => $searchModelAttachs,
            'dataProviderAttachs' => $dataProviderAttachs,
        ]);

    }

    /**
     * Deletes an existing PaymentOrder model.
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
        }
        catch (Exception $e)
        {
            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción eliminando el elemento'));
            $transaction->rollBack();
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the PaymentOrder model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PaymentOrder the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PaymentOrder::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend','La página solicitada no existe'));
        }
    }

    /**
    * Bulk Deletes for existing PaymentOrder models.
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

    public function actionGetResumeOrder($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = PaymentOrder::getResumePaymentOrder($id);

        return \Yii::$app->response->data = [
            'total_subtotal'=> GlobalFunctions::formatNumber($model->subtotal,2),
            'total_tax'=> GlobalFunctions::formatNumber($model->tax_amount,2),
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

        if (!is_array($ids) && !empty($ids))
            $ids = explode(',', $ids);

        $orders = PaymentOrder::find()->where(['id'=>$ids])->all();
        $data = '';
        foreach ($orders as $ord)
        {
            $detalles = ItemPaymentOrder::find()->where(['payment_order_id'=>$ord->id])->all();

            if (!empty($data))
                $data .= '<pagebreak>';

            $data .= $this->renderPartial('_pdf', [
                'orden'=>$ord,
                'detalles'=>$detalles,
                'logo'=>$logo,
                'moneda'=> $moneda,
                'original'=> $original,
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
                    'title' => 'Órdenes de Compra',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => false,
                ],
                // call mPDF methods on the fly
                'methods' => [
                    'SetTitle' => Yii::t('backend','Órdenes de Compra'),
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    'SetFooter' => ['|'.Yii::t('backend','Página').' {PAGENO}|'],
                ],
            ]);

            return $pdf->render();
        }
        else
        {
            if(!file_exists("uploads/payment_order/") || !is_dir("uploads/payment_order/")){
                try{
                    FileHelper::createDirectory("uploads/payment_order/", 0777);
                }catch (\Exception $exception){
                    Yii::info("Error handling Faqs folder resources");
                }
            }

            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'destination' => Pdf::DEST_FILE,
                'content' => $data,
                'filename' => $filename,
                'options' => [
                    // any mpdf options you wish to set
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => false,
                ],
                // call mPDF methods on the fly
                'methods' => [
                    'SetTitle' => Yii::t('backend','Órdenes de Compra'),
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    'SetFooter' => ['|'.Yii::t('backend','Página').' {PAGENO}|'],
                ],
            ]);
            $pdf->render();

            return Url::to($filename);
        }
    }

    /**
     * Funcion para generar el pdf de Rececion de mercancias
     */
    public function actionReport($id)
    {
        $model = $this->findModel($id);

        $reception_items = ReceptionItemPo::find()
            ->select([
                'reception_item_po.id',
                'product.bar_code',
                'product.supplier_code',
                'item_payment_order.description',
                'item_payment_order.quantity',
            ])
            ->innerJoin('item_payment_order', 'reception_item_po.item_payment_order_id = item_payment_order.id')
            ->innerJoin('product','item_payment_order.product_id = product.id')
            ->where(['item_payment_order.payment_order_id'=>$id])
            ->all();
        $logo = '<img src="'. Setting::getUrlLogoBySettingAndType(1) .'" width="75" height="45">';

        $content = $this->renderPartial('_pdf_reception', [
            'model' => $model,
            'reception_items' => $reception_items,
        ]);

        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE,
            'destination' => Pdf::DEST_BROWSER,
            'content' => $content,
            'filename' => 'Recep.'.date('Y-m-d').'pdf',
            'options' => [
                'defaultheaderline' => 0,
                'setAutoTopMargin' => 'stretch',
                'showWatermarkText' => false,
            ],
            // call mPDF methods on the fly
            'methods' => [
                'SetHeader'=>[$logo.' '.Yii::t('backend','Reporte de recepción de mercancías').' | | '.Yii::t('backend','Fecha').': '.GlobalFunctions::getCurrentDate('d/m/Y')],
                'SetTitle' => Yii::t('backend','Recepción de mercancía'),
                'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                'SetFooter' => ['|'.Yii::t('backend','Página').' {PAGENO}|'],
            ],
        ]);

        return $pdf->render();
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

        $payment_orders = PaymentOrder::find()
            ->where(['id'=>$ids])
            ->andWhere(['status_payment_order_id' => UtilsConstants::PAYMENT_ORDER_STATUS_APPROVED])
            ->all();

        $count_elements = count($payment_orders);
        $count_ids = count($ids);

        if($count_elements === 0)
        {
            GlobalFunctions::addFlashMessage('warning',Yii::t('backend','Las órdenes de compra seleccionadas deben estar APROBADAS para enviarlas'));
            return $this->redirect(['index']);
        }
        elseif($count_elements < $count_ids)
        {
            GlobalFunctions::addFlashMessage('warning',Yii::t('backend','Algunas órdenes de compra seleccionadas no se enviaron porque no están APROBADAS'));
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
        $count_elements = count($payment_orders);

        foreach ($payment_orders AS $key => $model)
        {
            $file_name = 'uploads/payment_order/po_'.$model->number.'-'.time().'.pdf';
            $file_pdf = $this->Viewpdf($model->id, $is_original, $currency,'file',$file_name);

            $result_send = $model->sendEmail($file_pdf);

            if($result_send === UtilsConstants::SEND_MAIL_RESPONSE_TYPE_EXCEPTION)
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('common','Ha ocurrido un error. No se ha podido establecer la conexión con el servidor de correo'));
                GlobalFunctions::deleteFile($file_name);
                return $this->redirect(['index']);
            }
            elseif($result_send === UtilsConstants::SEND_MAIL_RESPONSE_TYPE_CUSTOM)
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','El proveedor {name} no tiene datos de contacto definidos para el envío de la orden',['name'=>$model->supplier->name]));
                GlobalFunctions::deleteFile($file_name);
                return $this->redirect(['index']);
            }
            elseif($result_send === UtilsConstants::SEND_MAIL_RESPONSE_TYPE_ERROR)
            {
                $send_ok = false;
                $name_error_send= $name_error_send.'['.$model->number.'] ';
                $count_name_error_send++;
            }

            GlobalFunctions::deleteFile($file_name);
        }

        if($send_ok)
        {
            if($count_elements === 1)
            {
                GlobalFunctions::addFlashMessage('success',Yii::t('backend','Orden de compra enviada correctamente'));
            }
            else
            {
                GlobalFunctions::addFlashMessage('success',Yii::t('backend','Órdenes de compra enviadas correctamente'));
            }
        }
        else
        {
            if($count_elements === 1)
            {
                if($count_name_error_send===1)
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error enviando la orden de compra').': <b>'.$name_error_send.'</b>');
                }
            }
            else
            {
                if($count_name_error_send===1)
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error enviando la orden de compra').': <b>'.$name_error_send.'</b>');
                }
                elseif($count_name_error_send>1)
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error enviando las órdenes de compras').': <b>'.$name_error_send.'</b>');
                }
            }
        }

        return $this->redirect(['index']);
    }
}
