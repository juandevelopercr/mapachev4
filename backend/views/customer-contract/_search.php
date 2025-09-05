<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\business\CustomerContractSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="customer-contract-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'customer_id') ?>

    <?= $form->field($model, 'contract') ?>

    <?= $form->field($model, 'confirmation_number') ?>

    <?= $form->field($model, 'lugar_recogida') ?>

    <?php // echo $form->field($model, 'unidad_asignada') ?>

    <?php // echo $form->field($model, 'placa_unidad_asignada') ?>

    <?php // echo $form->field($model, 'fecha_recogida') ?>

    <?php // echo $form->field($model, 'fecha_devolucion') ?>

    <?php // echo $form->field($model, 'iva') ?>

    <?php // echo $form->field($model, 'porciento_descuento') ?>

    <?php // echo $form->field($model, 'naturaleza_descuento') ?>

    <?php // echo $form->field($model, 'decuento_fijo') ?>

    <?php // echo $form->field($model, 'total_comprobante') ?>

    <?php // echo $form->field($model, 'estado') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
