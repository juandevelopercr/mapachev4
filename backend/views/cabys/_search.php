<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\CabysSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="cabys-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'category1') ?>

    <?= $form->field($model, 'description1') ?>

    <?= $form->field($model, 'category2') ?>

    <?= $form->field($model, 'description2') ?>

    <?php // echo $form->field($model, 'category3') ?>

    <?php // echo $form->field($model, 'description3') ?>

    <?php // echo $form->field($model, 'category4') ?>

    <?php // echo $form->field($model, 'description4') ?>

    <?php // echo $form->field($model, 'category5') ?>

    <?php // echo $form->field($model, 'description5') ?>

    <?php // echo $form->field($model, 'category6') ?>

    <?php // echo $form->field($model, 'description6') ?>

    <?php // echo $form->field($model, 'category7') ?>

    <?php // echo $form->field($model, 'description7') ?>

    <?php // echo $form->field($model, 'category8') ?>

    <?php // echo $form->field($model, 'description8') ?>

    <?php // echo $form->field($model, 'code') ?>

    <?php // echo $form->field($model, 'description_service') ?>

    <?php // echo $form->field($model, 'tax') ?>

    <?php // echo $form->field($model, 'status')->checkbox() ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Buscar'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('backend', 'Resetear'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
