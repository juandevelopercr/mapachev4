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
use common\models\User;
use backend\models\business\Entry;
use backend\models\business\Supplier;
use backend\models\nomenclators\BranchOffice;

/* @var $this yii\web\View */
/* @var $model backend\models\business\XmlImported */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box-body">
<?php 
 $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
        
         
    <?= $form->field($model, 'currency_code')->textInput(['maxlength' => true]) ?>
         
    <?= 
        $form->field($model, "currency_change_value")->widget(NumberControl::classname(), [
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
    ?>                
             
    <?= $form->field($model, 'invoice_key')->textInput(['maxlength' => true]) ?>
         
    <?= $form->field($model, 'invoice_activity_code')->textInput(['maxlength' => true]) ?>
         
    <?= $form->field($model, 'invoice_consecutive_number')->textInput(['maxlength' => true]) ?>
         
    <?= $form->field($model, 'invoice_date')->textInput(['maxlength' => true]) ?>
            
    <?=
        $form->field($model, "user_id")->widget(Select2::classname(), [
            "data" => User::getSelectMap(),
            "language" => Yii::$app->language,
            "options" => ["placeholder" => "----", "multiple"=>false],
            "pluginOptions" => [
                "allowClear" => true
            ],
        ]);
    ?>
                
    <?=
        $form->field($model, "entry_id")->widget(Select2::classname(), [
            "data" => Entry::getSelectMap(),
            "language" => Yii::$app->language,
            "options" => ["placeholder" => "----", "multiple"=>false],
            "pluginOptions" => [
                "allowClear" => true
            ],
        ]);
    ?>
             
    <?= $form->field($model, 'xml_file')->textInput(['maxlength' => true]) ?>
         
    <?= $form->field($model, 'supplier_identification')->textInput(['maxlength' => true]) ?>
         
    <?= $form->field($model, 'supplier_identification_type')->textInput(['maxlength' => true]) ?>
         
    <?= $form->field($model, 'supplier_name')->textInput(['maxlength' => true]) ?>
         
    <?= $form->field($model, 'supplier_province_code')->textInput(['maxlength' => true]) ?>
         
    <?= $form->field($model, 'supplier_canton_code')->textInput(['maxlength' => true]) ?>
         
    <?= $form->field($model, 'supplier_district_code')->textInput(['maxlength' => true]) ?>
         
    <?= $form->field($model, 'supplier_barrio_code')->textInput(['maxlength' => true]) ?>
          
    <?= 
        $form->field($model, "supplier_other_signals")->widget(CKEditor::className(), [
            "preset" => "custom",
            "clientOptions" => [
                "toolbar" => GlobalFunctions::getToolBarForCkEditor(),
            ],
        ])
    ?>
             
    <?= $form->field($model, 'supplier_phone_country_code')->textInput(['maxlength' => true]) ?>
         
    <?= $form->field($model, 'supplier_phone')->textInput(['maxlength' => true]) ?>
         
    <?= $form->field($model, 'supplier_email')->textInput(['maxlength' => true]) ?>
         
    <?= $form->field($model, 'invoice_condition_sale_code')->textInput(['maxlength' => true]) ?>
         
    <?= $form->field($model, 'invoice_credit_time_code')->textInput(['maxlength' => true]) ?>
         
    <?= $form->field($model, 'invoice_payment_method_code')->textInput(['maxlength' => true]) ?>
            
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
<div class="box-footer">
    <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> '.Yii::t('backend','Crear') : '<i class="fa fa-pencil"></i> '.Yii::t('yii', 'Update'), ['class' => 'btn btn-default btn-flat']) ?>
    <?= Html::a('<i class="fa fa-remove"></i> '.Yii::t('backend','Cancelar'),['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Cancelar')]) ?>
</div>
<?php ActiveForm::end(); ?>

