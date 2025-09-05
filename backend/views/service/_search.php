<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\business\ServiceSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="service-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'code') ?>

    <?= $form->field($model, 'cabys_id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'unit_type_id') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'discount_amount') ?>

    <?php // echo $form->field($model, 'nature_discount') ?>

    <?php // echo $form->field($model, 'tax_type_id') ?>

    <?php // echo $form->field($model, 'tax_rate_type_id') ?>

    <?php // echo $form->field($model, 'tax_rate_percent') ?>

    <?php // echo $form->field($model, 'tax_amount') ?>

    <?php // echo $form->field($model, 'exoneration_document_type_id') ?>

    <?php // echo $form->field($model, 'number_exoneration_doc') ?>

    <?php // echo $form->field($model, 'name_institution_exoneration') ?>

    <?php // echo $form->field($model, 'exoneration_date') ?>

    <?php // echo $form->field($model, 'exoneration_purchase_percent') ?>

    <?php // echo $form->field($model, 'exonerated_tax_amount') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Buscar'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('backend', 'Resetear'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
