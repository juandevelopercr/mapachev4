<?php

namespace backend\controllers;

use backend\models\business\Invoice;
use backend\models\business\Product;
use backend\models\business\ProductHasBranchOffice;
use backend\models\business\Service;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\UtilsConstants;
use Yii;
use backend\models\business\ItemInvoice;
use backend\models\business\ItemInvoiceSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\helpers\Url;
use yii\db\Exception;

/**
 * ItemInvoiceTpvController implements the CRUD actions for ItemInvoice model.
 */
class ItemInvoiceTpvController extends Controller
{

    /**
     * Finds the ItemInvoice model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ItemInvoice the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ItemInvoice::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend', 'La página solicitada no existe'));
        }
    }

    /**********************************************************************************************
    / 									METODOS AJAX PARA CONTACTOS
    /**********************************************************************************************/

    /**
     * Creates a new ItemInvoice model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */

    public function actionCreate_ajax()
    {
        Yii::$app->response->format = 'json';

        if (Yii::$app->request->post()) {

            $array_posted = Yii::$app->request->post('ItemInvoiceTpvForm');

            $product_service_id = $array_posted['product_service'];
            $quantity_label = $price_type_label = '';
            $explode = explode('-', $product_service_id);

            if (isset($product_service_id) && !empty($product_service_id)) {

                if ($explode[0] == 'P') {
                    $model_reference = Product::findOne($explode[1]);

                    $item_exist = ItemInvoice::find()->where(['invoice_id' => $array_posted['invoice_id'], 'product_id' => $explode[1], 'unit_type_id' => $array_posted['unit_type_id']])->one();
                    if ($item_exist !== null) {
                        $model = $item_exist;                        
                        $model->product_id = $explode[1];
                        $model->code = $model_reference->code;
                        $model->unit_type_id = (isset($array_posted['unit_type_id']) && !empty($array_posted['unit_type_id'])) ? $array_posted['unit_type_id'] : $model_reference->unit_type_id;
                        $model->user_id = Yii::$app->user->id;
                        $model->invoice_id = $array_posted['invoice_id'];
                        $model->quantity += $array_posted['quantity'];
                        $request_quantity = $model->quantity;
                        $model->price_type = (isset($array_posted['price_type']) && !empty($array_posted['price_type'])) ? $array_posted['price_type'] : UtilsConstants::CUSTOMER_ASSIGN_PRICE_1;
                    } else {
                        $model = new ItemInvoice;
                        $model->product_id = $explode[1];
                        $model->code = $model_reference->code;
                        $model->unit_type_id = (isset($array_posted['unit_type_id']) && !empty($array_posted['unit_type_id'])) ? $array_posted['unit_type_id'] : $model_reference->unit_type_id;
                        $model->user_id = Yii::$app->user->id;
                        $model->invoice_id = $array_posted['invoice_id'];
                        $model->quantity = $array_posted['quantity'];
                        $request_quantity = $model->quantity;
                        $model->price_type = (isset($array_posted['price_type']) && !empty($array_posted['price_type'])) ? $array_posted['price_type'] : UtilsConstants::CUSTOMER_ASSIGN_PRICE_1;

                        /*    
                        if (isset($model->unit_type_id)) {
                            $unit_type_code = $model->unitType->code;
    
                            if ($unit_type_code == 'CAJ' || $unit_type_code == 'CJ') {
                                if (isset($model_reference->quantity_by_box)) {
                                    $request_quantity *= $model_reference->quantity_by_box;
                                    $quantity_label = ' [1x' . $model_reference->quantity_by_box . ']';
                                    //$model->price_unit = $model_reference->getPriceByType($model->price_type) * $request_quantity;
                                }
                            } elseif ($unit_type_code == 'BULT' || $unit_type_code == 'PAQ') {
                                if (isset($model_reference->package_quantity)) {
                                    $request_quantity *= $model_reference->package_quantity;
                                    $quantity_label = ' [1x' . $model_reference->package_quantity . ']';
                                    //$model->price_unit = $model_reference->getPriceByType($model->price_type) * $request_quantity;
                                }
                            }
                        } 
                        */                                         
                    }
                    
                    if (isset($model->price_type)) {
                        $price_type_label = UtilsConstants::getPriceTypeMiniLabel($model->price_type);                        
                        $model->price_unit = $model_reference->getPriceByTypeAndUnitType($model->price_type, $model->unit_type_id);
                    }

                    $model->description = $model_reference->description . ' <b>' . $price_type_label . ' ' . $quantity_label . '</b>';

                } elseif ($explode[0] == 'S') {
                    $item_exist = ItemInvoice::find()->where(['invoice_id' => $array_posted['invoice_id'], 'service_id' => $explode[1]])->one();
                    if ($item_exist !== null) {
                        $model = $item_exist;
                        $model->service_id = $explode[1];
                        $model_reference = Service::findOne($explode[1]);
                        $model->code = $model_reference->code;
                        $model->description = $model_reference->name;
                        $model->quantity = $array_posted['quantity'];
                        $request_quantity = $model->quantity;
                        $model->price_unit = $model_reference->price;
                    } else {
                        $model = new ItemInvoice;
                        $model->service_id = $explode[1];
                        $model_reference = Service::findOne($explode[1]);
                        $model->code = $model_reference->code;
                        $model->description = $model_reference->name;
                        $model->quantity = $array_posted['quantity'];
                        $request_quantity = $model->quantity;
                        $model->price_unit = $model_reference->price;
                    }
                }
            }

            if ($model_reference !== null) {                
                $model->invoice_id = $array_posted['invoice_id'];
                //$model->quantity = $array_posted['quantity'];
                $model->unit_type_id = (isset($array_posted['unit_type_id']) && !empty($array_posted['unit_type_id'])) ? $array_posted['unit_type_id'] : $model_reference->unit_type_id;

                $percent_iva = $model_reference->getPercentIvaToApply();

                //$default_price = (isset($model_reference->price1) && !empty($model_reference->price1)) ? $model_reference->price1 : $model_reference->price;
                //$model->price_unit = (isset($model->price_unit) && !empty($model->price_unit)) ? $model->price_unit : $default_price;

                if ($explode[0] == 'S') {
                    $model->discount_amount = $model_reference->getDiscount();
                    $model->nature_discount = $model_reference->nature_discount;
                } else {
                    $model->discount_amount = $model_reference->getDiscount();
                    $model->nature_discount = $model_reference->nature_discount;
                }
                if (is_null($model->nature_discount) || empty($model->nature_discount))
                    $model->nature_discount = '-';

                // Se aplica el descuento a nivel de producto o servicio
                
                $subtotal = $model->price_unit * $request_quantity - $model->discount_amount;
                $model->subtotal = (isset($subtotal) && !empty($subtotal)) ? $subtotal : 0;

                $tax_calculate = $subtotal * ($percent_iva / 100);
                $tax = (isset($tax_calculate) && !empty($tax_calculate)) ? $tax_calculate : 0;

                $exonerated = $tax * ($model_reference->exoneration_purchase_percent / 100);
                $exonerated_tax_amount = (isset($exonerated) && !empty($exonerated)) ? $exonerated : 0;
                $model->exonerate_amount = $exonerated_tax_amount;
                $model->exoneration_purchase_percent = (int)$model_reference->exoneration_purchase_percent;
                $model->exoneration_document_type_id = $model_reference->exoneration_document_type_id;
                $model->number_exoneration_doc = $model_reference->number_exoneration_doc;
                $model->name_institution_exoneration = $model_reference->name_institution_exoneration;
                $model->exoneration_date = $model_reference->exoneration_date;

                $model->tax_amount = $tax;
                $model->tax_rate_percent = $model_reference->tax_rate_percent;
                $model->tax_type_id = $model_reference->tax_type_id;
                $model->tax_rate_type_id = $model_reference->tax_rate_type_id;
                $model->price_total = $subtotal + $tax - $exonerated_tax_amount;
            }

            if ($model->save()) {
                //Actualizar los totales de la factura
                $invoice = Invoice::find()->where(['id'=>$model->invoice_id])->one();                
                $invoice->save(false);
                $model->refresh();

                $msg = 'Se ha creado el registro satisfactoriamente';
                $type = 'success';
                $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
                //$item_createid = $model->id;

            } else {
                $msg = 'Ha ocurrido un error al intentar crear el registro';
                $type = 'danger';
                $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
                //$item_createid = '';
            }

            return [
                'message' => $msg,
                'type' => $type,
                'titulo' => "Informaci&oacute;n <hr class=\"kv-alert-separator\">",
                //'id'=>$item_createid,
            ];
        }
    }

    /**
     * Updates an existing ItemInvoice() model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate_ajax($id)
    {
        $model = ItemInvoice::findOne($id);
        Yii::$app->response->format = 'json';

        if ($model->load(Yii::$app->request->post())) {
            $quantity_label = $price_type_label = '';
            $request_quantity = $model->quantity;

            if (isset($model->product_id) && !empty($model->product_id)) {
                $model_reference = Product::findOne($model->product_id);
                
                /*
                if (isset($model->unit_type_id)) {
                    $unit_type_code = $model->unitType->code;

                    // Se asigna el precio por defecto para una unidad 
                    //$model->price_unit = $model_reference->getPriceByType($model->price_type);

                    if ($unit_type_code == 'CAJ' || $unit_type_code == 'CJ') {
                        if (isset($model_reference->quantity_by_box)) {
                            $request_quantity *= $model_reference->quantity_by_box;
                            $quantity_label = ' [1x' . $model_reference->quantity_by_box . ']';
                        }
                    } elseif ($unit_type_code == 'BULT' || $unit_type_code == 'PAQ') {
                        if (isset($model_reference->package_quantity)) {
                            $request_quantity *= $model_reference->package_quantity;
                            $quantity_label = ' [1x' . $model_reference->package_quantity . ']';
                        }
                    }
                }
                */
                if (isset($model->price_type)) {
                    $price_type_label = UtilsConstants::getPriceTypeMiniLabel($model->price_type);
                }

                $model->description = $model_reference->description . ' <b>' . $price_type_label . ' ' . $quantity_label . '</b>';
            }
            if (isset($model->service_id) && !empty($model->service_id)) {
                $model_reference = Service::findOne($model->service_id);
            }

            if ($model_reference !== null) {
                if (is_null($model->discount_amount) || empty($model->discount_amount))
                    $model->discount_amount = 0;
                if (is_null($model->nature_discount) || empty($model->nature_discount))
                    $model->nature_discount = '-';

                $percent_iva = $model_reference->getPercentIvaToApply();

                //$default_price = (isset($model_reference->price1) && !empty($model_reference->price1)) ? $model_reference->price1 : $model_reference->price;
                //$model->price_unit = (isset($model->price_unit) && !empty($model->price_unit)) ? $model->price_unit : $default_price;
               
                $subtotal = $model->price_unit * $request_quantity - $model->discount_amount;
                $model->subtotal = (isset($subtotal) && !empty($subtotal)) ? $subtotal : 0;                

                $tax_calculate = $subtotal * ($percent_iva / 100);
                $tax = (isset($tax_calculate) && !empty($tax_calculate)) ? $tax_calculate : 0;

                $exonerated = $tax * ($model_reference->exoneration_purchase_percent / 100);
                $exonerated_tax_amount = (isset($exonerated) && !empty($exonerated)) ? $exonerated : 0;
                $model->exonerate_amount = $exonerated_tax_amount;
                $model->exoneration_purchase_percent = (int)$model_reference->exoneration_purchase_percent;
                $model->exoneration_document_type_id = $model_reference->exoneration_document_type_id;
                $model->number_exoneration_doc = $model_reference->number_exoneration_doc;
                $model->name_institution_exoneration = $model_reference->name_institution_exoneration;
                $model->exoneration_date = $model_reference->exoneration_date;

                $model->tax_amount = $tax;
                $model->tax_rate_percent = $model_reference->tax_rate_percent;
                $model->tax_type_id = $model_reference->tax_type_id;
                $model->tax_rate_type_id = $model_reference->tax_rate_type_id;
                $model->price_total = $subtotal + $tax - $exonerated_tax_amount;
            }

            if ($model->save()) {
                //Actualizar los totales de la factura
                $invoice = Invoice::find()->where(['id'=>$model->invoice_id])->one();                
                $invoice->save(false);                
                $model->refresh();
                $msg = 'Se ha actualizado el registro satisfactoriamente';
                $type = 'success';
                $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
            } else {
                $msg = 'Ha ocurrido un error al intentar actualizar el registro';
                $type = 'danger';
                $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
            }
            return [
                'message' => $msg,
                'type' => $type,
                'titulo' => "Informaci&oacute;n <hr class=\"kv-alert-separator\">",
            ];
        } else {

            return $this->renderAjax('_form-item', [
                'model' => $model,
            ]);
        }
    }  

    public function actionDeletemultiple_ajax()
    {
        $ids = (array)Yii::$app->request->post('ids');
        Yii::$app->response->format = 'json';
        if (!$ids) {
            return;
        }

        $eliminados = 0;
        $noeliminados = 0;
        foreach ($ids as $id)
        {
            $model = ItemInvoice::findOne($id);     
            $invoice_id = $model->invoice_id; 
            if ($model->delete()) {
                //Actualizar los totales de la factura
                $invoice = Invoice::find()->where(['id'=>$invoice_id])->one();                
                $invoice->save(false);

                $eliminados++;
            } else {
                $noeliminados++;
            }
        }

        $msg = $eliminados > 1 ? 'Se han eliminado '.$eliminados: 'Se ha eliminado '.$eliminados;
        $msg .= $eliminados > 1 ? ' registros <br />' : ' registro <br />';
        if ($noeliminados >= 1)
        {
            $msg .= $noeliminados > 1 ?  $noeliminados.' Registros no pudieron ser eliminados' : ' Registro no pudo ser eliminado';
            $type = 'warning';
        }
        else
            $type = 'success';

        return [
            'message' => $msg,
            'type'=> $type,
            'titulo'=>"Informaci&oacute;n <hr class=\"kv-alert-separator\">",
        ];
    }

    public function actionDelete_ajax($id)
    {
        Yii::$app->response->format = 'json';

        $eliminados = 0;
        $noeliminados = 0;

        $model = ItemInvoice::findOne($id);
        $invoice_id = $model->invoice_id;
        if ($model->delete()) {
            //Actualizar los totales de la factura
            $invoice = Invoice::find()->where(['id' => $invoice_id])->one();
            $invoice->save(false);
            $eliminados++;
        } else {
            $noeliminados++;
        }

        $msg = $eliminados > 1 ? 'Se han eliminado ' . $eliminados : 'Se ha eliminado ' . $eliminados;
        $msg .= $eliminados > 1 ? ' registros <br />' : ' registro <br />';
        if ($noeliminados >= 1) {
            $msg .= $noeliminados > 1 ?  $noeliminados . ' Registros no pudieron ser eliminados' : ' Registro no pudo ser eliminado';
            $type = 'warning';
        } else
            $type = 'success';

        return [
            'message' => $msg,
            'type' => $type,
            'titulo' => "Informaci&oacute;n <hr class=\"kv-alert-separator\">",
        ];
    }
}
