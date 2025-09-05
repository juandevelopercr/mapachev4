<?php

use yii\helpers\Html;
use common\widgets\DetailView;
use mdm\admin\components\Helper;
use common\models\GlobalFunctions;
use kartik\tabs\TabsX;
use backend\models\nomenclators\Cabys;
use backend\models\nomenclators\Family;
use backend\models\nomenclators\Category;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\BranchOffice;
use backend\models\business\Supplier;
use backend\models\nomenclators\InventoryType;
use backend\models\nomenclators\TaxType;
use backend\models\nomenclators\TaxRateType;
use backend\models\nomenclators\ExonerationDocumentType;
use kartik\dialog\Dialog;
use backend\models\business\ProductHasBranchOffice;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Product */

?>

<div class="row">
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('min_quantity').': </b>'.$model->min_quantity ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('max_quantity').': </b>'.$model->max_quantity ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('package_quantity').': </b>'.$model->package_quantity ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('quantity_by_box').': </b>'.$model->quantity_by_box ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="col-md-12 custom-padding-5">
            <?= '<b>'.$model->getAttributeLabel('price').': </b>'.GlobalFunctions::formatNumber($model->price,2) ?>
        </div>
        <div class="col-md-12 custom-padding-5">
            <?= '<b>'.$model->getAttributeLabel('price_custom').': </b>'.GlobalFunctions::formatNumber((isset($model->price_custom))? $model->price_custom : 0,2) ?>
        </div>
        <div class="col-md-12 custom-padding-5">
            <?= '<b>'.$model->getAttributeLabel('percent_detail').': </b>'.GlobalFunctions::formatNumber((isset($model->percent_detail))? $model->percent_detail : 0,2) ?>
        </div>
        <div class="col-md-12 custom-padding-5">
            <?= '<b>'.$model->getAttributeLabel('price_detail').': </b>'.GlobalFunctions::formatNumber((isset($model->price_detail))? $model->price_detail : 0,2) ?>
        </div>
    </div>

    <div class="col-md-4">
        <div class="row">

            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('percent1').': </b>'.GlobalFunctions::formatNumber((isset($model->percent1))? $model->percent1 : 0,2) ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('price1').': </b>'.GlobalFunctions::formatNumber((isset($model->price1))? $model->price1 : 0,2) ?>
            </div>

            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('percent2').': </b>'.GlobalFunctions::formatNumber((isset($model->percent2))? $model->percent2 : 0,2) ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('price2').': </b>'.GlobalFunctions::formatNumber((isset($model->price2))? $model->price2 : 0,2) ?>
            </div>

            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('percent3').': </b>'.GlobalFunctions::formatNumber((isset($model->percent3))? $model->percent3 : 0,2) ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('price3').': </b>'.GlobalFunctions::formatNumber((isset($model->price3))? $model->price3 : 0,2) ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('percent4').': </b>'.GlobalFunctions::formatNumber((isset($model->percent4))? $model->percent4 : 0,2) ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('price4').': </b>'.GlobalFunctions::formatNumber((isset($model->price4))? $model->price4 : 0,2) ?>
            </div>
        </div>
    </div>

</div>
