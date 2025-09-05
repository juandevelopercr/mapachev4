<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use common\models\GlobalFunctions;

/* @var $model backend\models\business\SupplierBankInformation */
?>



<div class="row item_group">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('banck_name').': </b>'.$model->banck_name ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('customer_account').': </b>'.$model->customer_account ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('checking_account').': </b>'.$model->checking_account ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('mobile_account').': </b>'.$model->mobile_account ?>
            </div>
        </div>
    </div>
</div>

<br>