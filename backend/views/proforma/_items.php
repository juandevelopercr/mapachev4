<?php

use common\models\GlobalFunctions;
use backend\models\nomenclators\UtilsConstants;

/* @var $model backend\models\business\ItemProforma */
//die(var_dump($model->price_type));
?>

<div class="row item_group">
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('code').': </b>'.$model->code ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('description').': </b>'.$model->description ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('quantity').': </b>'.GlobalFunctions::formatNumber($model->quantity,2) ?>
            </div>
            <?php
                $unit_type = (isset($model->unit_type_id))? $model->unitType->code : '';
                $price_type = (isset($model->price_type))? UtilsConstants::getCustomerAsssignPriceSelectType($model->price_type) : '';
            ?>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('unit_type_id').': </b>'.$unit_type ?>
            </div>
            <div class="col-md-12 custom-padding-5">            
                <?= '<b>'.$model->getAttributeLabel('price_type').': </b>'.$price_type ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('price_unit').': </b>'.GlobalFunctions::formatNumber($model->price_unit,2) ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('discount_amount').': </b>'.GlobalFunctions::formatNumber($model->discount_amount,2) ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('subtotal').': </b>'.GlobalFunctions::formatNumber($model->subtotal,2) ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('tax_amount').': </b>'.GlobalFunctions::formatNumber($model->tax_amount,2) ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('exonerate_amount').': </b>'.GlobalFunctions::formatNumber($model->exonerate_amount,2) ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('price_total').': </b>'.GlobalFunctions::formatNumber($model->price_total,2) ?>
            </div>
        </div>
    </div>
</div>

<br>