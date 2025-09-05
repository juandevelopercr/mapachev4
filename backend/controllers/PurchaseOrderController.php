<?php

namespace backend\controllers;

use backend\components\ApiBCCR;
use backend\models\business\Invoice;
use backend\models\business\ItemInvoice;
use backend\models\business\ItemPurchaseOrder;
use backend\models\business\ItemPurchaseOrderSearch;
use backend\models\business\PaymentMethodHasInvoice;
use backend\models\business\PaymentMethodHasPurchaseOrder;
use backend\models\business\CollectorHasPurchaseOrder;
use backend\models\business\SellerHasInvoice;
use backend\models\business\CollectorHasInvoice;
use backend\models\nomenclators\PaymentMethod;
use backend\models\business\Product;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use backend\models\settings\Setting;
use common\models\User;
use kartik\mpdf\Pdf;
use Yii;
use backend\models\business\PurchaseOrder;
use backend\models\business\PurchaseOrderSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\db\Exception;
use yii\web\Response;

/**
 * PurchaseOrderController implements the CRUD actions for PurchaseOrder model.
 */
class PurchaseOrderController extends Controller
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
            //'clearFilterState' => \thrieu\grid\ClearFilterStateBehavior::className(),
        ];
    }

    /**
     * Lists all PurchaseOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseOrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PurchaseOrder model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModelItems = new ItemPurchaseOrderSearch(['purchase_order_id' => $id]);
        $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);

        return $this->render('view', [
            'model' => $model,
            'dataProviderItems' => $dataProviderItems,
        ]);
    }

    /**
     * Creates a new PurchaseOrder model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PurchaseOrder();
        $model->loadDefaultValues();
        $model->scenario = 'create';
        $model->is_editable = 1;
        $model->consecutive = $model->generateConsecutive();
        $model->status = UtilsConstants::PURCHASE_ORDER_STATUS_STARTED;
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
                if(PurchaseOrder::find()->select(['consecutive'])->where(['consecutive' => $model->consecutive])->exists())
                {
                    $model->consecutive = $model->generateConsecutive();
                }

                if($model->save())
                {
                    PaymentMethodHasPurchaseOrder::updateRelation($model,[],'payment_methods','payment_method_id');

                    CollectorHasPurchaseOrder::updateRelation($model, [], 'collectors', 'collector_id');

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
     * Updates an existing PurchaseOrder model.
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
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','La orden de pedido solicitada no puede ser actualizada'));
                    return $this->redirect(['index']);
                }
            }

            $searchModelItems = new ItemPurchaseOrderSearch();
            $searchModelItems->purchase_order_id = $model->id;
            $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);

            //BEGIN payment method has purchase_order
            $payment_methods_assigned = PaymentMethodHasPurchaseOrder::getPaymentMethodByPurchaseOrderId($id);

            $payment_methods_assigned_ids= [];
            foreach ($payment_methods_assigned as $value)
            {
                $payment_methods_assigned_ids[]= $value['payment_method_id'];
            }
            
            $model->payment_methods = $payment_methods_assigned_ids;
            //END payment method has purchase_order

            //BEGIN collector has collector
            $collector_assigned = CollectorHasPurchaseOrder::getCollectorByPurchaseOrderId($id);

            $collector_assigned_ids = [];
            foreach ($collector_assigned as $value) {
                $collector_assigned_ids[] = $value['collector_id'];
            }

            $model->collectors = $collector_assigned_ids;
            //END seller method has collector            

            $old_status = (int)$model->status;

            if ($model->load(Yii::$app->request->post()))
            {
                $transaction = \Yii::$app->db->beginTransaction();

                try
                {
                    PaymentMethodHasPurchaseOrder::updateRelation($model, $payment_methods_assigned,'payment_methods','payment_method_id');

                    CollectorHasPurchaseOrder::updateRelation($model, $collector_assigned, 'collectors', 'collector_id');

                    if($model->save())
                    {
                        /*
                        $new_status = (int) $model->status;
                        if($old_status !== $new_status && $new_status === UtilsConstants::PURCHASE_ORDER_STATUS_STARTED)
                        {
                            $model->verifyStock();
                        }
                        */
                        //$model->verifyStock();    

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
     * Deletes an existing PurchaseOrder model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->status != UtilsConstants::PURCHASE_ORDER_STATUS_FINISHED)
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
            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','No se puede eliminar una orden con estado finalizada'));

        return $this->redirect(['index']);
    }

    /**
     * Finds the PurchaseOrder model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PurchaseOrder the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PurchaseOrder::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend','La página solicitada no existe'));
        }
    }

    /**
    * Bulk Deletes for existing PurchaseOrder models.
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
                    if ($model->status == UtilsConstants::PURCHASE_ORDER_STATUS_FINISHED)
                    {                        
                        $contNameErrorDelete++;
                        $nameErrorDelete = $nameErrorDelete. Yii::t('backend','No se puede eliminar una orden con estado finalizada');          
                        $deleteOK=false;             
                    }
                    else
                    {
                        if(!$model->delete())
                        {
                            $deleteOK=false;
                            $nameErrorDelete= $nameErrorDelete.'['.$model->id.'] ';
                            $contNameErrorDelete++;
                        }
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

    public function actionGetResumePurchaseOrder($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = PurchaseOrder::getResumePurchaseOrder($id);
        $order = PurchaseOrder::findOne($id);
        $percent_discount = (isset($order->discount_percent) && !empty($order->discount_percent))? $order->discount_percent : 0;
        if($percent_discount === 0)
        {
            $discount_order = 0;
        }
        else
        {
            $discount_order = $model->subtotal * ($percent_discount / 100);
        }

        $total_price = $model->subtotal + $model->tax_amount - $discount_order;

        return \Yii::$app->response->data = [
            'total_subtotal'=> GlobalFunctions::formatNumber($model->subtotal,2),
            'total_tax'=> GlobalFunctions::formatNumber($model->tax_amount,2),
            'total_discount'=> GlobalFunctions::formatNumber($discount_order,2),
            'total_exonerate'=> GlobalFunctions::formatNumber($model->exonerate_amount,2),
            'total_price'=> GlobalFunctions::formatNumber($total_price,2),
        ];
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionClone($id, $noItem = 0)
    {
        $model = $this->findModel($id);
        $clone_model = new PurchaseOrder();
        $clone_model->attributes = $model->attributes;
        $clone_model->consecutive = $clone_model->generateConsecutive();
        $clone_model->status = UtilsConstants::PURCHASE_ORDER_STATUS_STARTED;

        $transaction = \Yii::$app->db->beginTransaction();

        try
        {
            //BEGIN payment method has purchase_order
            $payment_methods_assigned = PaymentMethodHasPurchaseOrder::getPaymentMethodByPurchaseOrderId($id);

            $payment_methods_assigned_ids= [];
            foreach ($payment_methods_assigned as $value)
            {
                $payment_methods_assigned_ids[]= $value['payment_method_id'];
            }

            $clone_model->payment_methods = $payment_methods_assigned_ids;
            //END payment method has purchase_order


            //BEGIN collector has collector
            $collector_assigned = CollectorHasPurchaseOrder::getCollectorByPurchaseOrderId($id);

            $collector_assigned_ids = [];
            foreach ($collector_assigned as $value) {
                $collector_assigned_ids[] = $value['collector_id'];
            }

            $clone_model->collectors = $collector_assigned_ids;
            //END seller method has collector   


            if($clone_model->save())
            {
                if ($noItem == 0)
                {
                    //clonar los items de la orden de pedido y asociarlos a la nueva
                    $items_associates = ItemPurchaseOrder::findAll(['purchase_order_id' => $id]);

                    foreach ($items_associates AS $index => $item)
                    {
                        $new_item = new ItemPurchaseOrder();
                        $new_item->attributes = $item->attributes;
                        $new_item->purchase_order_id = $clone_model->id;
                        $new_item->save();
                    }
                }

                PaymentMethodHasPurchaseOrder::updateRelation($clone_model,[],'payment_methods','payment_method_id');

                CollectorHasPurchaseOrder::updateRelation($clone_model, [], 'collectors', 'collector_id');                

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
     * Funcion para generar el pdf de Preparación de mercancías
     */
    public function actionPreparation_pdf($ids)
    {
        if (!is_array($ids) && !empty($ids))
            $ids = explode(',', $ids);

        $items = PurchaseOrder::find()
            ->select([
                'SUM(item_purchase_order.quantity) AS quantity',
                'item_purchase_order.unit_type_id AS unit_type_id',
                'unit_type.code AS unit_type',
                'item_purchase_order.code',
                'product.description',
                'product.id AS product_id',
                'product.quantity_by_box AS quantity_by_box',
                'product.package_quantity AS package_quantity',
            ])
            ->where(['purchase_order.id' => $ids])
            ->innerJoin('item_purchase_order','item_purchase_order.purchase_order_id = purchase_order.id')
            ->innerJoin('product','product.id = item_purchase_order.product_id')
            ->innerJoin('unit_type','unit_type.id = item_purchase_order.unit_type_id')
            ->groupBy(' 
                product.id,
                item_purchase_order.unit_type_id,
                unit_type.code,
                item_purchase_order.code'
            )
            ->orderBy('product.id')
            ->asArray()
            ->all();


        $logo = '<img src="'. Setting::getUrlLogoBySettingAndType(1) .'" width="75" height="45">';

        $content = $this->renderPartial('_pdf_preparation', [
            'items' => $items
        ]);

        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE,
            'destination' => Pdf::DEST_BROWSER,
            'content' => $content,
            //'cssFile' => '@backend/web/css/reportes-pdf.css',
            'filename' => 'PreparaciónDeMercancías.'.date('Y-m-d').'pdf',
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
                'SetHeader'=>[$logo.' '.Yii::t('backend','Reporte de preparación de mercancías').' | | '.Yii::t('backend','Fecha').': '.GlobalFunctions::getCurrentDate('d/m/Y')],
                'SetTitle' => Yii::t('backend','Preparación de mercancía'),
                'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                'SetFooter' => ['|'.Yii::t('backend','Página').' {PAGENO}|'],
            ],
        ]);

        return $pdf->render();
    }

    /**
     * @param $id
     * @return Response
     */
    public function actionFacturar($id)
    {
        $model = $this->findModel($id);

        $transaction = \Yii::$app->db->beginTransaction();

        try
        {
            $invoice = new Invoice();
            $invoice->attributes = $model->attributes;
            $invoice->status = UtilsConstants::INVOICE_STATUS_PENDING;
            $invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_NOT_SENT;
            $invoice->emission_date = date('Y-m-d H:i:s');

            /*
            if($invoice->collector_id === null)
            {
                $invoice->collector_id = (isset($model->customer->collector_id) && !empty($model->customer->collector_id))? $model->customer->collector_id : null;
            }
            if($invoice->seller_id === null)
            {
                $invoice->seller_id = (isset($model->customer->seller_id) && !empty($model->customer->seller_id))? $model->customer->seller_id : null;
            }
            */

            $invoice->invoice_type = (isset($model->customer->pre_invoice_type) && !empty($model->customer->pre_invoice_type))? $model->customer->pre_invoice_type : UtilsConstants::PRE_INVOICE_TYPE_INVOICE;
            $invoice->route_transport_id = (isset($model->customer->route_transport_id) && !empty($model->customer->route_transport_id))? $model->customer->route_transport_id : null;
            $invoice->consecutive = $invoice->generateConsecutive();

            //BEGIN payment method has purchase_order
            $payment_methods_assigned = PaymentMethodHasPurchaseOrder::getPaymentMethodByPurchaseOrderId($id);

            $payment_methods_assigned_ids= [];
            foreach ($payment_methods_assigned as $value)
            {
                $payment_methods_assigned_ids[]= $value['payment_method_id'];
            }

            $invoice->payment_methods = $payment_methods_assigned_ids;
            //END payment method has purchase_order


            //BEGIN collector has collector
            $collector_assigned = CollectorHasPurchaseOrder::getCollectorByPurchaseOrderId($id);

            $collector_assigned_ids = [];
            foreach ($collector_assigned as $value) {
                $collector_assigned_ids[] = $value['collector_id'];
            }

            $invoice->sellers = $collector_assigned_ids;

            // Aidicionar los compradores que sean los mismos vendedores
            $invoice->collectors = $collector_assigned_ids;
            //END seller method has collector   
            
            // Le asigno el vendedor
            //$invoice->sellers = $collector_assigned_ids;


            if($invoice->save())
            {
                PaymentMethodHasInvoice::updateRelation($invoice,[],'payment_methods','payment_method_id');

                SellerHasInvoice::updateRelation($invoice, [], 'sellers', 'seller_id');

                CollectorHasInvoice::updateRelation($invoice, [], 'collectors', 'collector_id');

                //clonar los items de la purchase_order y asociarlos a la nueva factura
                $items_purchase_order_associated = ItemPurchaseOrder::findAll(['purchase_order_id' => $id]);

                foreach ($items_purchase_order_associated AS $index => $item_purchase_order)
                {
                    $new_item_invoice = new ItemInvoice();
                    $new_item_invoice->attributes = $item_purchase_order->attributes;
                    $new_item_invoice->invoice_id = $invoice->id;
                    $new_item_invoice->save();
                }

                $model->status = UtilsConstants::PURCHASE_ORDER_STATUS_FINISHED;                
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
