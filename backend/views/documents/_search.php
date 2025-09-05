<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\business\DocumentsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="documents-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'receiver_id') ?>

    <?= $form->field($model, 'key') ?>

    <?= $form->field($model, 'consecutive') ?>

    <?= $form->field($model, 'transmitter') ?>

    <?php // echo $form->field($model, 'transmitter_identification_type') ?>

    <?php // echo $form->field($model, 'transmitter_identification') ?>

    <?php // echo $form->field($model, 'document_type') ?>

    <?php // echo $form->field($model, 'emission_date') ?>

    <?php // echo $form->field($model, 'reception_date') ?>

    <?php // echo $form->field($model, 'url_xml') ?>

    <?php // echo $form->field($model, 'url_pdf') ?>

    <?php // echo $form->field($model, 'url_ahc') ?>

    <?php // echo $form->field($model, 'currency') ?>

    <?php // echo $form->field($model, 'change_type') ?>

    <?php // echo $form->field($model, 'total_tax') ?>

    <?php // echo $form->field($model, 'total_invoice') ?>

    <?php // echo $form->field($model, 'transmitter_email') ?>

    <?php // echo $form->field($model, 'message_detail') ?>

    <?php // echo $form->field($model, 'tax_condition') ?>

    <?php // echo $form->field($model, 'total_amount_tax_credit') ?>

    <?php // echo $form->field($model, 'total_amount_applicable_expense') ?>

    <?php // echo $form->field($model, 'attempts_making_set') ?>

    <?php // echo $form->field($model, 'attempts_making_get') ?>

    <?php // echo $form->field($model, 'state_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
