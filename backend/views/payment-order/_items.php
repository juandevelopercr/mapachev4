<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use common\models\GlobalFunctions;
use common\models\User;

/* @var $model backend\models\business\ItemPaymentOrder */
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
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('unit_type_id').': </b>'.$model->product->unitType->code ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('price_unit').': </b>'.GlobalFunctions::formatNumber($model->price_unit,2) ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="row">

            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('subtotal').': </b>'.GlobalFunctions::formatNumber($model->subtotal,2) ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('discount_amount').': </b>'.GlobalFunctions::formatNumber($model->discount_amount,2) ?>
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