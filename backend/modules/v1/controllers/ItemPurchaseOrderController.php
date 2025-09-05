<?php

namespace backend\modules\v1\controllers;

use backend\models\business\ItemPurchaseOrder;
use backend\models\business\ItemPurchaseOrderForm;
use backend\models\business\ItemPurchaseOrderSearch;
use backend\models\business\Product;
use backend\models\business\Service;
use backend\models\nomenclators\UtilsConstants;
use backend\modules\v1\ApiUtilsFunctions;
use common\models\GlobalFunctions;
use yii\db\Exception;
use yii\filters\auth\QueryParamAuth;
use Yii;
use common\models\User;
use yii\web\ForbiddenHttpException;

/**
 * ItemPurchaseOrderController for the `v1` module
 */
class ItemPurchaseOrderController extends ApiController
{
    public $modelClass = 'backend\models\business\ItemPurchaseOrder';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
        ];
        return $behaviors;
    }

    public $serializer = [
        'class' => 'backend\modules\v1\CustomSerializer',
        'collectionEnvelope' => 'items',
    ];

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        $actions['index'] = [
            'class' => 'yii\rest\IndexAction',
            'modelClass' => $this->modelClass,
            'prepareDataProvider' => function () {
                $searchModel = new ItemPurchaseOrderSearch();
                return $searchModel->search(Yii::$app->request->queryParams);
            },
        ];

        return $actions;
    }

    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET'],
            'create' => ['POST'],
            'update' => ['POST', 'PUT', 'PATCH'],
            'delete' => ['POST', 'DELETE'],
        ];
    }

    public function actionView($id)
    {
        $model = ItemPurchaseOrder::findOne($id);

        if ($model !== null) {
            return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS, '', $model);
        } else {
            ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_NOTFOUND);
        }
    }

    public function actionCreate()
    {
        $model = new ItemPurchaseOrder();
        $form_model = new ItemPurchaseOrderForm();
        $form_model->load(Yii::$app->getRequest()->getBodyParams(), '');

        $model->user_id = Yii::$app->user->id;
        $model->purchase_order_id = $form_model->purchase_order_id;
        $model->quantity = $form_model->quantity;
        $product_service_id = $form_model->product_service;
        $product_code = $form_model->product_code;
        $quantity_label = $price_type_label = '';
        $request_quantity = $model->quantity;
        $model->price_type = (isset($form_model->price_type) && !empty($form_model->price_type))? $form_model->price_type : UtilsConstants::CUSTOMER_ASSIGN_PRICE_1;

        if(isset($product_service_id) && !empty($product_service_id))
        {
            $explode = explode('-',$product_service_id);
            if($explode[0] == 'P')
            {
                $model->product_id = $explode[1];
                $model_reference = Product::findOne($explode[1]);
                $model->code = $model_reference->bar_code;
                $model->unit_type_id = (isset($form_model->unit_type_id) && !empty($form_model->unit_type_id))? $form_model->unit_type_id : $model_reference->unit_type_id;

                if(isset($model->unit_type_id))
                {
                    $unit_type_code = $model->unitType->code;

                    if($unit_type_code == 'CAJ' || $unit_type_code == 'CJ')
                    {
                        if(isset($model_reference->quantity_by_box))
                        {
                            $request_quantity *= $model_reference->quantity_by_box;
                            $quantity_label = ' [1x'.$model_reference->quantity_by_box.']';
                        }
                    }
                    elseif($unit_type_code == 'BULT' || $unit_type_code == 'PAQ')
                    {
                        if(isset($model_reference->package_quantity))
                        {
                            $request_quantity *= $model_reference->package_quantity;
                            $quantity_label = ' [1x'.$model_reference->package_quantity.']';
                        }
                    }
                }

                if(isset($model->price_type))
                {
                    $price_type_label = UtilsConstants::getPriceTypeMiniLabel($model->price_type);
                    $model->price_unit = $model_reference->getPriceByType($model->price_type);
                }
                $model->description = $model_reference->description.' <b>'.$quantity_label.'</b>';
            }
            elseif($explode[0] == 'S')
            {
                $model->service_id = $explode[1];
                $model_reference = Service::findOne($explode[1]);
                $model->code = $model_reference->code;
                $model->description = $model_reference->name;
            }
        }
        elseif(isset($product_code) && !empty($product_code))
        {
            $model_reference = Product::find()->where(['bar_code' => $product_code])->one();
            if($model_reference !== null)
            {
                $model->product_id = $model_reference->id;
                $model->code = $model_reference->bar_code;
                $model->unit_type_id = (isset($form_model->unit_type_id) && !empty($form_model->unit_type_id))? $form_model->unit_type_id : $model_reference->unit_type_id;

                if(isset($model->unit_type_id))
                {
                    $unit_type_code = $model->unitType->code;

                    if($unit_type_code == 'CAJ' || $unit_type_code == 'CJ')
                    {
                        if(isset($model_reference->quantity_by_box))
                        {
                            $request_quantity *= $model_reference->quantity_by_box;
                            $quantity_label = ' [1x'.$model_reference->quantity_by_box.']';
                        }
                    }
                    elseif($unit_type_code == 'BULT' || $unit_type_code == 'PAQ')
                    {
                        if(isset($model_reference->package_quantity))
                        {
                            $request_quantity *= $model_reference->package_quantity;
                            $quantity_label = ' [1x'.$model_reference->package_quantity.']';
                        }
                    }
                }

                if(isset($model->price_type))
                {
                    $price_type_label = UtilsConstants::getPriceTypeMiniLabel($model->price_type);
                    $model->price_unit = $model_reference->getPriceByType($model->price_type);
                }
                $model->description = $model_reference->description.' <b>'.$quantity_label.'</b>';
            }
            else
            {
                $message = Yii::t('backend', 'No existe ningún producto con código').': '.$product_code;
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR, $message);
            }
        }

        if($model_reference !== null)
        {
            $percent_iva = 13;
            $default_price = (isset($model_reference->price1) && !empty($model_reference->price1))? $model_reference->price1 : $model_reference->price;
            $model->price_unit = (isset($model->price_unit) && !empty($model->price_unit))? $model->price_unit : $default_price;
            $model->discount_amount = 0;
            $subtotal = $request_quantity * $model->price_unit;
            $model->subtotal = (isset($subtotal) && !empty($subtotal))? $subtotal : 0;

            $tax_calculate = $subtotal * ($percent_iva / 100);
            $tax = (isset($tax_calculate) && !empty($tax_calculate))? $tax_calculate : 0;

            $exonerated = $tax * ($model_reference->exoneration_purchase_percent / 100);
            $exonerated_tax_amount = (isset($exonerated) && !empty($exonerated))? $exonerated : 0;
            $model->exonerate_amount = $exonerated_tax_amount;

            $tax_to_apply = $tax - $exonerated_tax_amount;
            $model->tax_amount = $tax_to_apply;

            $model->price_total = $subtotal + $tax_to_apply - $model->discount_amount;
        }

        if (!$model->validate()) {
            $message = Yii::t('backend', 'Error creando el elemento');
            return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR, $message, $model->getFirstErrors());
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {

            if ($model->save()) {

                $transaction->commit();
                $model->refresh();
                $message = Yii::t('backend', 'Elemento creado correctamente');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS, $message, $model);
            } else {
                $message = Yii::t('backend', 'Error creando el elemento');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR, $message, $model->getFirstErrors());
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            $message = Yii::t('backend', 'Error, ha ocurrido una excepción creando el elemento');
            return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR, $message, $e->getMessage());
        }
    }

    public function actionUpdate($id)
    {
        $model = ItemPurchaseOrder::findOne($id);

        if (isset($model) && !empty($model))
        {
            $params = $this->getRequestParamsAsArray();

            $this->checkAccess($this->action->id, $model, $params);
            $model->load($params, '');

            $quantity_label = $price_type_label = '';
            $request_quantity = $model->quantity;

            if(isset($model->product_id) && !empty($model->product_id))
            {
                $model_reference = Product::findOne($model->product_id);
                if(isset($model->unit_type_id))
                {
                    $unit_type_code = $model->unitType->code;

                    if($unit_type_code == 'CAJ' || $unit_type_code == 'CJ')
                    {
                        if(isset($model_reference->quantity_by_box))
                        {
                            $request_quantity *= $model_reference->quantity_by_box;
                            $quantity_label = ' [1x'.$model_reference->quantity_by_box.']';
                        }
                    }
                    elseif($unit_type_code == 'BULT' || $unit_type_code == 'PAQ')
                    {
                        if(isset($model_reference->package_quantity))
                        {
                            $request_quantity *= $model_reference->package_quantity;
                            $quantity_label = ' [1x'.$model_reference->package_quantity.']';
                        }
                    }
                }
                if(isset($model->price_type))
                {
                    $price_type_label = UtilsConstants::getPriceTypeMiniLabel($model->price_type);
                    $model->price_unit = $model_reference->getPriceByType($model->price_type);
                }
                $model->description = $model_reference->description.' <b>'.$quantity_label.'</b>';
            }
            if(isset($model->service_id) && !empty($model->service_id))
            {
                $model_reference = Service::findOne($model->service_id);
            }

            if($model_reference !== null)
            {
                $percent_iva = 13;
                $default_price = (isset($model_reference->price1) && !empty($model_reference->price1))? $model_reference->price1 : $model_reference->price;
                $model->price_unit = (isset($model->price_unit) && !empty($model->price_unit))? $model->price_unit : $default_price;
                $model->discount_amount = 0;
                $subtotal = $request_quantity * $model->price_unit;
                $model->subtotal = (isset($subtotal) && !empty($subtotal))? $subtotal : 0;

                $tax_calculate = $subtotal * ($percent_iva / 100);
                $tax = (isset($tax_calculate) && !empty($tax_calculate))? $tax_calculate : 0;

                $exonerated = $tax * ($model_reference->exoneration_purchase_percent / 100);
                $exonerated_tax_amount = (isset($exonerated) && !empty($exonerated))? $exonerated : 0;
                $model->exonerate_amount = $exonerated_tax_amount;

                $tax_to_apply = $tax - $exonerated_tax_amount;
                $model->tax_amount = $tax_to_apply;

                $model->price_total = $subtotal + $tax_to_apply - $model->discount_amount;
            }

            if (!$model->validate()) {
                $message = Yii::t('backend', 'Error actualizando el elemento');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR, $message, $model->getFirstErrors());
            }

            $transaction = \Yii::$app->db->beginTransaction();

            try {

                if ($model->save())
                {
                    $transaction->commit();
                    $model->refresh();
                    $message = Yii::t('backend', 'Elemento actualizado correctamente');
                    return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS, $message, $model);
                } else {
                    $message = Yii::t('backend', 'Error actualizando el elemento');
                    return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR, $message, $model->getFirstErrors());
                }
            } catch (Exception $e) {
                $transaction->rollBack();
                $message = Yii::t('backend', 'Error, ha ocurrido una excepción creando el elemento');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR, $message, $e->getMessage());
            }
        } else {
            ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_NOTFOUND);
        }
    }

    public function actionDelete($id)
    {
        $model = ItemPurchaseOrder::findOne($id);
        $params = Yii::$app->request->queryParams;
        $this->checkAccess($this->action->id, $model, $params);

        if ($model !== null) {
            if ($model->delete()) {
                $message = Yii::t('backend', 'Elemento eliminado correctamente');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS, $message, []);

            } else {
                $message = Yii::t('backend', 'Error eliminando el elemento');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR, $message, $model->getFirstErrors());
            }
        } else {
            ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_NOTFOUND);
        }
    }


    public function actionGetPriceTypePreview($product_service, $price_type = null)
    {
        $explode = explode('-', $product_service);

        if($explode[0] === 'P')
        {
            $product = Product::findOne($explode[1]);
            $type = (isset($price_type) && !empty($price_type))? $price_type : UtilsConstants::CUSTOMER_ASSIGN_PRICE_1;
            $result = $product->getPriceByType($type);
        }
        elseif ($explode[0] === 'S')
        {
            $service = Service::findOne($explode[1]);
            $result = $service->price;
        }
        else
        {
            $result = '0';
        }

        return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS, '', '¢ '.$result);
    }

    /**
     * @param string $action
     * @param null $model
     * @param array $params
     * @return bool|void
     * @throws ForbiddenHttpException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (GlobalFunctions::getRol() !== User::ROLE_SUPERADMIN) {
            if (Yii::$app->user->id !== $model->user_id) {
                ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_FORBIDDEN);
            }
        }

        return true;
    }
}
