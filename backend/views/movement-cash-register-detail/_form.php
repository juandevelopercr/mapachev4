<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\business\MovementCashRegisterDetail */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="movement-cash-register-detail-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'movement_cash_register_id')->textInput() ?>

    <?= $form->field($model, 'value')->textInput() ?>

    <?= $form->field($model, 'count')->textInput() ?>

    <?= $form->field($model, 'comment')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
