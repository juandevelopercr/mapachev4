<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\business\CashRegisteSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="cash-register-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'box_id') ?>

    <?= $form->field($model, 'seller_id') ?>

    <?= $form->field($model, 'opening_date') ?>

    <?= $form->field($model, 'opening_time') ?>

    <?php // echo $form->field($model, 'closing_date') ?>

    <?php // echo $form->field($model, 'closing_time') ?>

    <?php // echo $form->field($model, 'initial_amount') ?>

    <?php // echo $form->field($model, 'end_amount') ?>

    <?php // echo $form->field($model, 'total_sales') ?>

    <?php // echo $form->field($model, 'status')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
