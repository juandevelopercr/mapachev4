<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\business\ManualInvoiceSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="manual-invoice-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'branch_office_id') ?>

    <?= $form->field($model, 'supplier_id') ?>

    <?= $form->field($model, 'currency_id') ?>

    <?= $form->field($model, 'consecutive') ?>

    <?php // echo $form->field($model, 'emission_date') ?>

    <?php // echo $form->field($model, 'observations') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'total_comprobante') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
