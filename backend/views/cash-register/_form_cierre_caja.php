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
use backend\models\nomenclators\Boxes;
use backend\models\nomenclators\BranchOffice;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\business\CashRegister */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box-body">
    <?php
    $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
    <input type="hidden" name="cash_register_id" value="<?= $cashRegister->id ?>">
    <input type="hidden" name="movement_type_id" value="<?= $movement_type_id ?>">
    <input type="hidden" name="MovementCashRegisterDetail[count]" value="1">
    <input type="hidden" name="movement_cash_register_id" value="<?= $movement->id ?>">
    
    <div class="row">
        <div class="col-md-3">
            <?=
            $form->field($model, "monto_inicial")->widget(NumberControl::classname(), [
                'readonly' => true,
                "maskedInputOptions" => [
                    "allowMinus" => false,
                    "groupSeparator" => ".",
                    "radixPoint" => ",",
                    "digits" => 2
                ],
                "displayOptions" => ["class" => "form-control kv-monospace"],
                "saveInputContainer" => ["class" => "kv-saved-cont"]
            ])->label('Monto Inicial')
            ?>
        </div>
        <div class="col-md-3">
            <?=
            $form->field($model, "monto_adicionado")->widget(NumberControl::classname(), [
                'readonly' => true,
                "maskedInputOptions" => [
                    "allowMinus" => false,
                    "groupSeparator" => ".",
                    "radixPoint" => ",",
                    "digits" => 2
                ],
                "displayOptions" => ["class" => "form-control kv-monospace"],
                "saveInputContainer" => ["class" => "kv-saved-cont"]
            ])->label('Monto entrada de efectivo')
            ?>
        </div>
        <div class="col-md-3">
            <?=
            $form->field($model, "monto_retirado")->widget(NumberControl::classname(), [
                'readonly' => true,
                "maskedInputOptions" => [
                    "allowMinus" => false,
                    "groupSeparator" => ".",
                    "radixPoint" => ",",
                    "digits" => 2
                ],
                "displayOptions" => ["class" => "form-control kv-monospace"],
                "saveInputContainer" => ["class" => "kv-saved-cont"]
            ])->label('Monto salida efectivo')
            ?>
        </div>
        <div class="col-md-3">
            <?=
            $form->field($model, "total_ventas")->widget(NumberControl::classname(), [
                'readonly' => true,
                "maskedInputOptions" => [
                    "allowMinus" => false,
                    "groupSeparator" => ".",
                    "radixPoint" => ",",
                    "digits" => 2
                ],
                "displayOptions" => ["class" => "form-control kv-monospace"],
                "saveInputContainer" => ["class" => "kv-saved-cont"]
            ])->label('Total de ventas')
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <?=
            $form->field($model, "monto_a_entregar")->widget(NumberControl::classname(), [
                'readonly' => true,
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
            $form->field($model, "value")->widget(NumberControl::classname(), [
                "maskedInputOptions" => [
                    "allowMinus" => false,
                    "groupSeparator" => ".",
                    "radixPoint" => ",",
                    "digits" => 2
                ],
                "displayOptions" => ["class" => "form-control kv-monospace"],
                "saveInputContainer" => ["class" => "kv-saved-cont"]
            ])->label('Cantidad a entregar')
            ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'comment')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

</div>
<div class="box-footer">
    <?= Html::submitButton('<i class="fa fa-plus"></i> ' . Yii::t('backend', 'Guardar'), ['class' => 'btn btn-default btn-flat']) ?>
    <?= Html::a('<i class="fa fa-remove"></i> ' . Yii::t('backend', 'Cancelar'), ['arqueo', 'box_id' => $cashRegister->box->id], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Cancelar')]) ?>
</div>
<?php ActiveForm::end(); ?>