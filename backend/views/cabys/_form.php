<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\builder\Form;
use kartik\widgets\FileInput;
use kartik\switchinput\SwitchInput;
use dosamigos\ckeditor\CKEditor;
use kartik\date\DatePicker;
use kartik\number\NumberControl;
use common\models\GlobalFunctions;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\Cabys */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box-body">
<?php 
 $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-7">
            <?= $form->field($model, 'description_service')->textarea() ?>
        </div>

        <div class="col-md-1">
            <?= $form->field($model, 'tax')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-md-2">
            <?=
            $form->field($model,"status")->widget(SwitchInput::classname(), [
                "type" => SwitchInput::CHECKBOX,
                "pluginOptions" => [
                    "onText"=> Yii::t("backend","Activo"),
                    "offText"=> Yii::t("backend","Inactivo")
                ]
            ])
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'category1')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-8">
            <?=
            $form->field($model, "description1")->textarea()
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'category2')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-8">
            <?=
            $form->field($model, "description2")->textarea()
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'category3')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-8">
            <?=
            $form->field($model, "description3")->textarea()
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'category4')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-8">
            <?=
            $form->field($model, "description4")->textarea()
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'category5')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-8">
            <?=
            $form->field($model, "description5")->textarea()
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'category6')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-8">
            <?=
            $form->field($model, "description6")->textarea()
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'category7')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-8">
            <?=
            $form->field($model, "description7")->textarea()
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'category8')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-8">
            <?=
            $form->field($model, "description8")->textarea()
            ?>
        </div>
    </div>

</div>
<div class="box-footer">
    <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> '.Yii::t('backend','Crear') : '<i class="fa fa-pencil"></i> '.Yii::t('yii', 'Update'), ['class' => 'btn btn-default btn-flat']) ?>
    <?= Html::a('<i class="fa fa-remove"></i> '.Yii::t('backend','Cancelar'),['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Cancelar')]) ?>
</div>
<?php ActiveForm::end(); ?>

