<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use common\models\GlobalFunctions;
use common\models\User;

/* @var $model backend\models\business\ItemEntry */
?>

<div class="row item_group">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('product_code').': </b>'.$model->product_code ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('product_description').': </b>'.$model->product_description ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('price').': </b>'.$model->price ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('entry_quantity').': </b>'.$model->entry_quantity ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="row">

            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('past_quantity').': </b>'.$model->past_quantity ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('past_price').': </b>'.$model->past_price ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('new_quantity').': </b>'.$model->new_quantity ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('user_id').': </b>'.User::getFullNameByUserId($model->user_id) ?>
            </div>
        </div>
    </div>
</div>

<br>