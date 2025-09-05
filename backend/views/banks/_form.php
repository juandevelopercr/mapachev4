<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\nomenclators\BranchOffice;
use kartik\select2\Select2;
use kartik\number\NumberControl;
use backend\models\business\Invoice;

/* @var $this yii\web\View */
/* @var $model app\models\business\PointsSale */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="box-body">
    <?php
    $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="box-footer">
        <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> ' . Yii::t('backend', 'Crear') : '<i class="fa fa-pencil"></i> ' . Yii::t('yii', 'Update'), ['class' => 'btn btn-default btn-flat']) ?>
        <?= Html::a('<i class="fa fa-remove"></i> ' . Yii::t('backend', 'Cancelar'), ['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Cancelar')]) ?>
    </div>
    <?php ActiveForm::end(); ?>

    <!-- /.box-body -->
</div>