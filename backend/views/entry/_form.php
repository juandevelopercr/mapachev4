<?php

use yii\helpers\Html;
use kartik\builder\Form;
use kartik\date\DatePicker;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\FileInput;
use dosamigos\ckeditor\CKEditor;
use kartik\number\NumberControl;
use common\models\GlobalFunctions;
use kartik\datecontrol\DateControl;
use kartik\switchinput\SwitchInput;
use backend\models\business\Supplier;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\UtilsConstants;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Entry */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box-body">
<?php 
 $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-7">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'order_purchase')->textInput(['maxlength' => true,'readonly'=>true]) ?>
                </div>
                <div class="col-md-6">
                    <?=
                    $form->field($model, "invoice_date")->widget(DateControl::classname(), [
                        "type" => DateControl::FORMAT_DATE
                    ])
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?=
                    $form->field($model, "supplier_id")->widget(Select2::classname(), [
                        "data" => Supplier::getSelectMap(),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple"=>false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ]);
                    ?>
                </div>
                <div class="col-md-6">
                    <?=
                    $form->field($model, "branch_office_id")->widget(Select2::classname(), [
                        "data" => BranchOffice::getSelectMap(),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple"=>false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ]);
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'invoice_number')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <?=
                    $form->field($model, "invoice_type")->widget(Select2::classname(), [
                        "data" => UtilsConstants::getInvoiceType(),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple"=>false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                        'disabled'=>!$model->isNewRecord
                    ]);
                    ?>
                </div> 
                <div class="col-md-3">
                    <?=
                    $form->field($model, "currency")->widget(Select2::classname(), [
                        "data" => Currency::getSelectMapCode(),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple" => false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ]);
                    ?>
                </div>                                
                <div class="col-md-3">
                    <?=
                    $form->field($model, "total_tax")->widget(NumberControl::classname(), [
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 2
                        ],
                        "readonly"=> true,
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>
                </div>                
                <div class="col-md-3">
                    <?=
                    $form->field($model, "amount")->widget(NumberControl::classname(), [
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 2,                            
                        ],
                        "readonly"=> true,
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>
                </div>
               
            </div>

        </div>
        <div class="col-md-5">
            <?=
            $form->field($model, "observations")->widget(CKEditor::className(), [
                "preset" => "custom",
                "clientOptions" => [
                    "toolbar" => GlobalFunctions::getToolBarForCkEditor(),
                ],
            ])
            ?>
        </div>
    </div>

</div>
<div class="box-footer">
    <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> '.Yii::t('backend','Crear') : '<i class="fa fa-pencil"></i> '.Yii::t('yii', 'Update'), ['class' => 'btn btn-default btn-flat']) ?>
    <?= Html::a('<i class="fa fa-remove"></i> '.Yii::t('backend','Cancelar'),['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Cancelar')]) ?>
</div>
<?php ActiveForm::end(); ?>

