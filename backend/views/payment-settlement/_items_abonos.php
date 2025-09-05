<?php

use common\models\GlobalFunctions;
use backend\models\nomenclators\UtilsConstants;

/* @var $model backend\models\business\ItemInvoice */
?>

<div class="row item_group">
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('emission_date').': </b>'.GlobalFunctions::formatDateToShowInSystem($model->emission_date) ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('reference').': </b>'.$model->reference ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('amount').': </b>'.GlobalFunctions::formatNumber($model->amount,2) ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('payment_method_id').': </b>'.$model->paymentMethod->name ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('bank_id').': </b>'.(!is_null($model->bank) ? $model->bank->name: '') ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('collector_id').': </b>'.(!is_null($model->collector) ? $model->collector->getFullName(): '-') ?>
            </div>  
        </div>
        <div class="row">          
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('comment').': </b>'.$model->comment ?>
            </div>
        </div>
    </div>
</div>

<br>