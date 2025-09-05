<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\switchinput\SwitchInput;
use kartik\number\NumberControl;
use common\models\GlobalFunctions;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use backend\models\nomenclators\BranchOffice;
use backend\models\business\Customer;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\Banks;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use common\models\User;
use backend\models\nomenclators\PaymentMethod;
use backend\models\nomenclators\Zone;
use backend\models\nomenclators\Boxes;
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
        <h3 class="box-title"><?= Yii::t('backend', 'Abono') ?></h3>

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
        <?= $form->field($model, 'invoice_id')->hiddenInput()->label(false); ?>
        <input type="hidden" id="htotal_amount" value="<?= $total ?>">
        <input type="hidden" id="habonado_amount" value="<?= $abonado ?>">

        <div class="row">
            <div class="col-md-4">
                <label>Total Factura: <?= GlobalFunctions::formatNumber($invoice->total_comprobante,2) ?> </label>
            </div>
            <div class="col-md-4">
                <label>Abonado: <span id="abonado_amount" style="color:green"><?= GlobalFunctions::formatNumber($abonado,2) ?></span> </label>
            </div>
            <div class="col-md-4">
                <label>Pendiente: <span id="pending_amount" style="color:red"><?= GlobalFunctions::formatNumber($pendiente,2) ?></span> </label>
            </div>
        </div>               
        <div class="row">
            <div class="col-md-12">
                <br />
            </div>
        </div>                
        <div class="row">
            <div class="col-md-4">
                <?=
                $form->field($model, "emission_date")->widget(DateControl::classname(), [
                    "type" => DateControl::FORMAT_DATE
                ])
                ?>
            </div>
            <div class="col-md-4">
                <?=
                $form->field($model, "payment_method_id")->widget(Select2::classname(), [
                    "data" => PaymentMethod::getSelectMap(),
                    "language" => Yii::$app->language,
                    'maintainOrder' => true,
                    "options" => [
                        "placeholder" => "----",
                        "multiple" => false
                    ],
                    "pluginOptions" => [
                        "allowClear" => true
                    ]
                ]);
                ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'reference')->textInput(['maxlength' => true, 'readonly' => false]); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?=
                $form->field($model, "bank_id")->widget(Select2::classname(), [
                    "data" => Banks::getSelectMap(true),
                    "language" => Yii::$app->language,
                    "options" => ["placeholder" => "----", "multiple" => false],
                    "pluginOptions" => [
                        "allowClear" => true
                    ],
                ]);
                ?>
            </div>

            <div class="col-md-4">
                <?=
                $form->field($model, "amount")->widget(NumberControl::classname(), [
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
            <div class="col-md-4">
                <?=
                $form->field($model, "collector_id")->widget(Select2::classname(), [
                    "data" => User::getSelectMapAgents(),
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
                <?= $form->field($model, 'comment')->textInput(['maxlength' => true, 'readonly' => false]) ?>    
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

<?php
$url_get_customers_info = Url::to(['/customer/get_info'], GlobalFunctions::URLTYPE);
$id_credit = ConditionSale::getIdCreditConditionSale();
$js = <<<JS
// get the form id and set the event
$(document).ready(function(e) {
   
  //setcontrols();
    
    $('#invoiceabonos-amount-disp').keyup(function(e) {    
          // Only ASCII character in that range allowed 
          var abono = $(this).val(); 
          abono = getValueByField(abono);  
          var abonado_amount = $("#habonado_amount").val();
          var total = $("#htotal_amount").val();
          
          if(!isNaN(abono)){	              
            var total_monto_abonado = parseFloat(abonado_amount) + parseFloat(abono);
            
            if (total_monto_abonado > total)
            {                                
                diferencia = parseFloat(total_monto_abonado) - parseFloat(total);
                abono = parseFloat(abono - diferencia);
                total_monto_abonado = parseFloat(abonado_amount) + parseFloat(abono);

                $("#invoiceabonos-amount-disp").val(abono.toFixed(2));
                $("#invoiceabonos-amount").val(abono.toFixed(2));                
                alert("El monto abonado supera el total de la factura. Se ha ajustado el valor del abono");
            }
            $("#abonado_amount").html(total_monto_abonado.toFixed(2));
            var pendiente = total - total_monto_abonado;
            $("#pending_amount").html(pendiente.toFixed(2));
          }
    });

    function getValueByField(value)
    {
        data = value.replace(/\./g,"");			
        data = data.replace(/\,/g,".");	
        data = parseFloat(data);
        return data;
    }

});
JS;
$this->registerJs($js);
?>