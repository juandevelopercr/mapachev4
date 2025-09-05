<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\switchinput\SwitchInput;
use kartik\number\NumberControl;
use common\models\GlobalFunctions;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\Boxes;
use backend\models\business\Customer;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use common\models\User;
use backend\models\nomenclators\PaymentMethod;
use backend\models\nomenclators\RouteTransport;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Invoice */
/* @var $form yii\widgets\ActiveForm */
/* @var $searchModelItems \backend\models\business\ItemInvoiceSearch */
/* @var $dataProviderItems yii\data\ActiveDataProvider */

?>

<div class="box box-warning box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('backend', 'Cuenta por Pagar') ?></h3>

        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
        <!-- /.box-tools -->
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <?php
        $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'key')->textInput(['maxlength' => true, 'readonly' => false]); ?>
            </div>
            <div class="col-md-3">
                <?=
                $form->field($model, "total_invoice")->widget(NumberControl::classname(), [
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
            </div>    
            <div class="col-md-3">
                <?=
                $form->field($model, "currency")->widget(Select2::classname(), [
                    "data" => ['CRC'=>'CRC', 'USD'=>'USD'],
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
                $form->field($model, "status")->widget(Select2::classname(), [
                    'disabled' => (Yii::$app->controller->action->id == 'create'),
                    "data" => UtilsConstants::getStatusAccountsPayable(),
                    "language" => Yii::$app->language,
                    "options" => ["placeholder" => "----", "multiple" => false],
                    "pluginOptions" => [
                        "allowClear" => true
                    ],
                ]);
                ?>
            </div>

        </div>


        <div class="box-footer">
            <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> ' . Yii::t('backend', 'Crear') : '<i class="fa fa-pencil"></i> ' . Yii::t('yii', 'Update'), ['class' => 'btn btn-default btn-flat']) ?>
            <?= Html::a('<i class="fa fa-remove"></i> ' . Yii::t('backend', 'Cancelar'), ['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Cancelar')]) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <!-- /.box-body -->
</div>

