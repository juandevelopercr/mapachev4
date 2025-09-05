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

/* @var $this yii\web\View */
/* @var $model backend\models\business\Product */

$label_tax_type = (isset($model->tax_type_id) && !empty($model->tax_type_id))? $model->taxType->code.' - '.$model->taxType->name : '';
$label_tax_rate_type = (isset($model->tax_rate_type_id) && !empty($model->tax_rate_type_id))? $model->taxRateType->code.' - '.$model->taxRateType->name : '';
$label_exoneration_document_type = (isset($model->exoneration_document_type_id) && !empty($model->exoneration_document_type_id))? $model->exonerationDocumentType->code.' - '.$model->exonerationDocumentType->name : '';
?>

<div class="row">

    <div class="col-md-4">
        <div class="col-md-12 custom-padding-5">
            <?= '<b>'.$model->getAttributeLabel('discount_amount').': </b>'.GlobalFunctions::formatNumber((isset($model->discount_amount))? $model->discount_amount : 0,2) ?>
        </div>
        <div class="col-md-12 custom-padding-5">
            <?= '<b>'.$model->getAttributeLabel('nature_discount').': </b>'.$model->nature_discount ?>
        </div>
    </div>

    <div class="col-md-4">
        <div class="row">

            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('tax_type_id').': </b>'.$label_tax_type ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('tax_rate_type_id').': </b>'.$label_tax_rate_type ?>
            </div>

            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('tax_rate_percent').': </b>'.GlobalFunctions::formatNumber((isset($model->tax_rate_percent))? $model->tax_rate_percent : 0,2) ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="row">

            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('exoneration_document_type_id').': </b>'.$label_exoneration_document_type ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('name_institution_exoneration').': </b>'.$model->name_institution_exoneration ?>
            </div>

            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('number_exoneration_doc').': </b>'.$model->number_exoneration_doc ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('exoneration_date').': </b>'.GlobalFunctions::formatDateToShowInSystem($model->exoneration_date) ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('exoneration_purchase_percent').': </b>'.GlobalFunctions::formatNumber((isset($model->exoneration_purchase_percent))? $model->exoneration_purchase_percent : 0) ?>
            </div>
        </div>
    </div>

</div>

