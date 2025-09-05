<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\business\CustomerContract */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="customer-contract-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'customer_id')->textInput() ?>

    <?= $form->field($model, 'contract')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'confirmation_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'lugar_recogida')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'unidad_asignada')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'placa_unidad_asignada')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fecha_recogida')->textInput() ?>

    <?= $form->field($model, 'fecha_devolucion')->textInput() ?>

    <?= $form->field($model, 'iva')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'porciento_descuento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'naturaleza_descuento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'decuento_fijo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'total_comprobante')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'estado')->dropDownList([ 'pendiente' => 'Pendiente', 'procesado' => 'Procesado', ], ['prompt' => '']) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
