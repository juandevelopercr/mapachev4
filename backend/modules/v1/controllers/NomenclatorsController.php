<?php

namespace backend\modules\v1\controllers;

use backend\models\business\ItemPurchaseOrderForm;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\Collector;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\PaymentMethod;
use backend\models\nomenclators\RouteTransport;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\UtilsConstants;
use backend\modules\v1\ApiUtilsFunctions;
use yii\rest\ActiveController;

/**
 * NomenclatorsController for the `v1` module
 */
class NomenclatorsController extends ActiveController
{
    public $modelClass = '';

    public function actionGet_status_purchase_order()
    {
        $data[] = UtilsConstants::getPurchaseOrderStatusSelectType();
        return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_INDEX_RESPONSE,'',$data);
    }

    public function actionGet_condition_sale()
    {
        $data = ConditionSale::find()->all();
        return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_INDEX_RESPONSE,'',$data);
    }

    public function actionGet_credit_days()
    {
        $data = CreditDays::find()->all();
        return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_INDEX_RESPONSE,'',$data);
    }

    public function actionGet_branch_office()
    {
        $data = BranchOffice::find()->all();
        return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_INDEX_RESPONSE,'',$data);
    }

    public function actionGet_currency()
    {
        $data = Currency::find()->all();
        return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_INDEX_RESPONSE,'',$data);
    }

    public function actionGet_delivery_time_type()
    {
        $data[] = UtilsConstants::getDeliveryTimesSelectType();
        return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_INDEX_RESPONSE,'',$data);
    }

    public function actionGet_collector()
    {
        $data = Collector::find()->all();
        return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_INDEX_RESPONSE,'',$data);
    }

    public function actionGet_payment_method()
    {
        $data = PaymentMethod::find()->all();
        return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_INDEX_RESPONSE,'',$data);
    }

    public function actionGet_route_transport()
    {
        $data = RouteTransport::find()->all();
        return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_INDEX_RESPONSE,'',$data);
    }

    public function actionGet_unit_type()
    {
        $data = UnitType::getSelectMap(true,true);
        return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS,'',$data);
    }

    public function actionGet_price_type()
    {
        $data = UtilsConstants::getCustomerAsssignPriceSelectType();
        return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS,'',$data);
    }

    public function actionGet_product_service()
    {
        $data = ItemPurchaseOrderForm::getSelectMap(false);
        return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS,'',$data);
    }

    public function actionSearch_product_service($name)
    {
        $data = ItemPurchaseOrderForm::searchByName($name,false);
        return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS,'',$data);
    }
}

