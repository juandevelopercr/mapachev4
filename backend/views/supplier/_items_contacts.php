<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use common\models\GlobalFunctions;

/* @var $model backend\models\business\SupplierContact */
?>



<div class="row item_group">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('name').': </b>'.$model->name ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('email').': </b>'.$model->email ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('department_id').': </b>'.$model->department->code.' - '.$model->department->name ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('job_position_id').': </b>'.$model->jobPosition->code.' - '.$model->jobPosition->name ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="row">

            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('phone').': </b>'.$model->phone ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('ext').': </b>'.$model->ext ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('cellphone').': </b>'.$model->cellphone ?>
            </div>
        </div>
    </div>
</div>

<br>