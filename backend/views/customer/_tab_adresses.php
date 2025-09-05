<?php

use backend\models\nomenclators\Canton;
use backend\models\nomenclators\Collector;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\CustomerClassification;
use backend\models\nomenclators\CustomerType;
use backend\models\nomenclators\Disctrict;
use backend\models\nomenclators\ExonerationDocumentType;
use backend\models\nomenclators\IdentificationType;
use backend\models\nomenclators\Province;
use backend\models\nomenclators\RouteTransport;
use backend\models\nomenclators\UtilsConstants;
use backend\models\settings\Issuer;
use common\models\GlobalFunctions;
use kartik\builder\Form;
use kartik\datecontrol\DateControl;
use kartik\depdrop\DepDrop;
use kartik\form\ActiveForm;
use kartik\number\NumberControl;
use kartik\select2\Select2;
use kartik\switchinput\SwitchInput;
use yii\helpers\Html;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $model backend\models\business\Customer */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box-body">



    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'country_code_phone')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-2">
            <?=
            $form->field($model, "phone")->widget(NumberControl::classname(), [
                "maskedInputOptions" => [
                    "allowMinus" => false,
                    "groupSeparator" => "",
                    "radixPoint" => "",
                    "digits" => 0,
                ],
                "displayOptions" => ["class" => "form-control kv-monospace"],
                "saveInputContainer" => ["class" => "kv-saved-cont"]
            ])
            ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'country_code_fax')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'fax')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-8">
            <?= $form->field($model, 'email_cc')->textArea(['rows' => 1]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?=
            $form->field($model, "province_id")->widget(Select2::classname(), [
                "data" => Province::getSelectMap(),
                "language" => Yii::$app->language,
                "options" => ["placeholder" => "----", "multiple" => false],
                "pluginOptions" => [
                    "allowClear" => true
                ],
            ]);
            ?>
        </div>

        <div class="col-md-4">
            <?= $form->field($model, 'canton_id')->widget(DepDrop::classname(), [
                'type' => DepDrop::TYPE_SELECT2,
                'data' => ($model->province_id > 0) ? Canton::getSelectMapSpecific($model->province_id) : array(),
                'options' => ['placeholder' => "----"],
                'select2Options' => ['pluginOptions' => ['allowClear' => true]],
                'pluginOptions' => [
                    'depends' => ['customer-province_id'],
                    'url' => Url::to(['/util/get_cantons'], GlobalFunctions::URLTYPE),
                    'params' => ['input-type-1', 'input-type-2']
                ]
            ]);
            ?>
        </div>
        <div class="col-md-4">

            <?= $form->field($model, 'disctrict_id')->widget(DepDrop::classname(), [
                'type' => DepDrop::TYPE_SELECT2,
                'data' => ($model->canton_id > 0) ? Disctrict::getSelectMapSpecific($model->canton_id) : array(),
                'options' => ['placeholder' => "----"],
                'select2Options' => ['pluginOptions' => ['allowClear' => true]],
                'pluginOptions' => [
                    'depends' => ['customer-province_id', 'customer-canton_id'],
                    'url' => Url::to(['/util/get_dictrict'], GlobalFunctions::URLTYPE),
                    'params' => ['input-type-1', 'input-type-2']
                ]
            ]);
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'other_signs')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

</div>