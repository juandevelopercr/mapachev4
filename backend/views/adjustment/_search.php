<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\business\AdjustmentSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="adjustment-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'consecutive') ?>

    <?= $form->field($model, 'product_id') ?>

    <?= $form->field($model, 'type') ?>

    <?= $form->field($model, 'past_quantity') ?>

    <?php // echo $form->field($model, 'entry_quantity') ?>

    <?php // echo $form->field($model, 'new_quantity') ?>

    <?php // echo $form->field($model, 'observations') ?>

    <?php // echo $form->field($model, 'user_id') ?>

    <?php // echo $form->field($model, 'origin_branch_office_id') ?>

    <?php // echo $form->field($model, 'target_branch_office_id') ?>

    <?php // echo $form->field($model, 'invoice_number') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Buscar'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('backend', 'Resetear'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
