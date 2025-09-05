<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use wbraganca\dynamicform\DynamicFormWidget;
use kartik\widgets\SwitchInput;
use backend\models\nomenclators\UtilsConstants;
use kartik\select2\Select2;
use backend\models\business\Sector;
use kartik\number\NumberControl;


/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\BranchOffice */
/* @var $model_auto backend\models\nomenclators\BranchOfficeAutomaticForm */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="box-body">
<?php 
 $form = ActiveForm::begin(['id' => 'dynamic-form','options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-12">
            <fieldset style="width: 100%; border: 1px solid #C0C0C0; padding-right: 15px; padding-left: 15px;">
                <legend style="width: auto; margin: 8px; border: 0; padding-right: 1%; padding-left: 1%; font-size: 16px; font-weight: bold; border: 1px solid #C0C0C0;"><?= Yii::t('backend','Sucursal') ?></legend>
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>
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
                    <div class="col-md-4">
                        <?= $form->field($model, 'description')->textarea() ?>
                    </div>
                </div>
            </fieldset>
        </div>

        <div class="col-md-6">
            <fieldset style="width: 100%; border: 1px solid #C0C0C0; margin: 5px;">
                <legend style="width: auto; margin: 8px; border: 0; padding-right: 1%; padding-left: 1%; font-size: 16px; font-weight: bold; border: 1px solid #C0C0C0;"><?= Yii::t('backend','Sectores') ?></legend>
                <div class="col-md-6">
                    <?= $form->field($model_auto, 'sector_name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model_auto, "sector_code_start")->widget(Select2::classname(), [
                        "data" => UtilsConstants::getAlphabetCodesSelectMap(),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple"=>false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model_auto, "sector_code_end")->widget(Select2::classname(), [
                        "data" => UtilsConstants::getAlphabetCodesSelectMap(),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple"=>false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ]) ?>
                </div>
            </fieldset>
        </div>

        <div class="col-md-6">
            <fieldset style="width: 100%; border: 1px solid #C0C0C0; margin: 5px;">
                <legend style="width: auto; margin: 8px; border: 0; padding-right: 1%; padding-left: 1%; font-size: 16px; font-weight: bold; border: 1px solid #C0C0C0;"><?= Yii::t('backend','Ubicaciones') ?></legend>
                <div class="col-md-6">
                    <?= $form->field($model_auto, 'location_name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-3">
                    <?=
                    $form->field($model_auto, "location_code_start")->widget(NumberControl::classname(), [
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 0,
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>
                </div>
                <div class="col-md-3">
                    <?=
                    $form->field($model_auto, "location_code_end")->widget(NumberControl::classname(), [
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 0,
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>
                </div>
            </fieldset>
        </div>
    </div>

</div>
<div class="box-footer">
    <?= Html::submitButton('<i class="fa fa-plus"></i> '.Yii::t('backend','Generar'), ['class' => 'btn btn-default btn-flat']) ?>
    <?= Html::a('<i class="fa fa-remove"></i> '.Yii::t('backend','Cancelar'),['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Cancelar')]) ?>
</div>
<?php ActiveForm::end(); ?>