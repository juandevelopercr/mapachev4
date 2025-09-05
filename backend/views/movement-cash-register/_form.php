<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\business\MovementCashRegister */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="movement-cash-register-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'cash_register_id')->textInput() ?>

    <?= $form->field($model, 'movement_type_id')->textInput() ?>

    <?= $form->field($model, 'movement_date')->textInput() ?>

    <?= $form->field($model, 'movement_time')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
