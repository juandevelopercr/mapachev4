<?php

use backend\models\nomenclators\ActividadEconomica;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\CustomerClassification;
use backend\models\nomenclators\CustomerType;
use backend\models\nomenclators\ExonerationDocumentType;
use backend\models\nomenclators\IdentificationType;
use backend\models\nomenclators\RouteTransport;
use backend\models\nomenclators\UtilsConstants;
use backend\models\settings\Issuer;
use common\models\User;
use kartik\datecontrol\DateControl;
use kartik\number\NumberControl;
use kartik\select2\Select2;
use kartik\switchinput\SwitchInput;


/* @var $this yii\web\View */
/* @var $model backend\models\business\Customer */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box-body">
    <input type="hidden" name="change_usd" id="change_usd" value="<?= Issuer::getChange_type_dollar() ?>" />

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'readonly' => true]) ?>
        </div>

        <div class="col-md-3">
            <?=
            $form->field($model, "pre_invoice_type")->widget(Select2::classname(), [
                "data" => UtilsConstants::getPreInvoiceSelectType(),
                "language" => Yii::$app->language,
                "options" => ["placeholder" => "----", "multiple" => false],
                "pluginOptions" => [
                    "allowClear" => true
                ],
            ]);
            ?>
        </div>
        <div class="col-md-7">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">

        <div class="col-md-3">
            <?= $form->field($model, 'commercial_name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-3">
            <?=
            $form->field($model, "identification_type_id")->widget(Select2::classname(), [
                "data" => IdentificationType::getSelectMap(),
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
            $form->field($model, "identification")->widget(NumberControl::classname(), [
                "maskedInputOptions" => [
                    "allowMinus" => false,
                    "groupSeparator" => "",
                    "radixPoint" => "",
                    "digits" => 0
                ],
                "displayOptions" => ["class" => "form-control kv-monospace"],
                "saveInputContainer" => ["class" => "kv-saved-cont"]
            ])
            ?>
        </div>
         <div class="col-md-3">
            <?=
            $form->field($model, "economicActivity")->widget(Select2::classname(), [
                "data" => ActividadEconomica::getSelectMap(),
                "language" => Yii::$app->language,
                "options" => ["placeholder" => "----", "multiple" => false],
                "pluginOptions" => [
                    "allowClear" => true
                ],
            ]);
            ?>
        </div>        
    </div>
    <div class="row">
        <div class="col-md-3">
            <?=
            $form->field($model, "condition_sale_id")->widget(Select2::classname(), [
                "data" => ConditionSale::getSelectMap(),
                "language" => Yii::$app->language,
                "options" => ["placeholder" => "----", "multiple" => false],
                "pluginOptions" => [
                    "allowClear" => true
                ],
            ]);
            ?>
        </div>        
        <div class="col-md-2">
            <?=
            $form->field($model, "credit_days_id")->widget(Select2::classname(), [
                "data" => CreditDays::getSelectMap(),
                "language" => Yii::$app->language,
                "options" => ["placeholder" => "----", "multiple" => false],
                "pluginOptions" => [
                    "allowClear" => true
                ],
            ]);
            ?>
        </div>        
    </div>
    
    <?php
    /*
    Esto lo deshailité porque lo que se exonera es el producto o el servicio no el cliente
    <div class="row">
        <div class="col-md-12" id="col-exonerado" style="display:block">
            <fieldset id="pedido_minimo_fieldset" style="width: 100%; border: 1px solid #C0C0C0;">
                <legend style="width: auto; margin: 8px; border: 0; padding-right: 1%; padding-left: 1%; font-size: 16px; font-weight: bold; border: 1px solid #C0C0C0;"><?= Yii::t('backend', 'Exoneración') ?></legend>
                <div class="col-md-2">
                    <?=
                    $form->field($model, "is_exonerate")->widget(SwitchInput::classname(), [
                        "type" => SwitchInput::CHECKBOX,
                        "pluginOptions" => [
                            "onText" => Yii::t("backend", "SI"),
                            "offText" => Yii::t("backend", "NO")
                        ]
                    ])
                    ?>
                </div>
                <div class="col-md-3">
                    <?=
                    $form->field($model, "exoneration_date")->widget(DateControl::classname(), [
                        "type" => DateControl::FORMAT_DATE
                    ])
                    ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'number_exoneration_doc')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'name_institution_exoneration')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-5">
                    <?=
                    $form->field($model, "exoneration_document_type_id")->widget(Select2::classname(), [
                        "data" => ExonerationDocumentType::getSelectMap(),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple" => false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ]);
                    ?>
                </div>
                <div class="col-md-2">
                    <?=
                    $form->field($model, "exoneration_purchase_percent")->widget(NumberControl::classname(), [
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 2,
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>
                </div>

            </fieldset>
        </div>
    </div>
    */
    ?>
</div>

<?php
$id_credit = ConditionSale::getIdCreditConditionSale();

//autofill values
$identification = '3101700831';
$name = 'RENT CAR';
$price_type = UtilsConstants::CUSTOMER_ASSIGN_PRICE_1;
$pre_invoice_type_ticket = UtilsConstants::PRE_INVOICE_TYPE_TICKET;

$model_identification_type = IdentificationType::findOne(['code' => '02']); //cedula juridica
$identification_type = ($model_identification_type) ? $model_identification_type->id : null;

$model_customer_type = CustomerType::findOne(['code' => '01']); //cliente general
$customer_type = ($model_customer_type) ? $model_customer_type->id : null;

$model_customer_clasif = CustomerClassification::findOne(['name' => 'A']); // A
$customer_clasif = ($model_customer_clasif) ? $model_customer_clasif->id : null;

$model_condition_sale = ConditionSale::findOne(['code' => '01']); //Contado
$condition_sale = ($model_condition_sale) ? $model_condition_sale->id : null;

$js = <<<JS
// get the form id and set the event
$(document).ready(function(e) {
   
  setcontrols();
    
  $("#customer-condition_sale_id").change(function() {
	setcontrols();
  });
  
  $("#customer-pre_invoice_type").change(function() {
	autofill();
  });

  function setcontrols()
  {		
 	 if ($("#customer-condition_sale_id").val() == "$id_credit")
     {
        //$("#customer-credit_amount_colon-disp").attr('disabled', false);
		//$("#customer-credit_amount_usd-disp").attr('disabled', false);	
		$("#customer-credit_days_id").attr('disabled', false);
     }
	 else
	 {
        /*
        $("#customer-credit_amount_colon-disp").attr('disabled', true);
		$("#customer-credit_amount_colon-disp").val(0);
		$("#customer-credit_amount_colon").val(0);

		$("#customer-credit_amount_usd-disp").attr('disabled', true);
		$("#customer-credit_amount_usd-disp").val(0);
		$("#customer-credit_amount_usd").val(0);
		*/
		$("#customer-credit_days_id").attr('disabled', true);			
	 }
  }
    /*
	$("#customer-credit_amount_colon-disp").keyup(function (e) {
		refresh_prices('usd');
	});   	
	
	$("#customer-credit_amount_usd-disp").keyup(function (e) {
		refresh_prices('colon');
	});   
    */		
	
	function refresh_prices(type)
	{		
	    var change_type = $("#change_usd").val();
	    
		if (type == 'usd')
		{			
			price = $("#customer-credit_amount_colon-disp").val().replace(/\./g,"");
		    price = price.replace(",",".");
		    change_value = price / change_type;
			
			$("#customer-credit_amount_usd-disp").val(change_value.toFixed(2));
			$("#customer-credit_amount_usd").val(change_value.toFixed(2));
		}
		else
		{
			price = $("#customer-credit_amount_usd-disp").val().replace(/\./g,"");
		    price = price.replace(",",".");
			change_value = price * change_type;
			$("#customer-credit_amount_colon-disp").val(change_value.toFixed(2));
			$("#customer-credit_amount_colon").val(change_value.toFixed(2));
		}	
	} 
	
    function autofill()
	{	    
		 if ($("#customer-pre_invoice_type").val() == "$pre_invoice_type_ticket")
         {
             $('#customer-identification_type_id').val("$identification_type").trigger('change.select2');
             $('#customer-customer_type_id').val("$customer_type").trigger('change.select2');
             $('#customer-customer_classification_id').val("$customer_clasif").trigger('change.select2');
             $('#customer-condition_sale_id').val("$condition_sale").trigger('change.select2');
            // $('#customer-price_assigned').val("$price_type").trigger('change.select2');
             $('#customer-name').val("$name");
             $('#customer-identification').val("$identification");
             $('#customer-identification-disp').val("$identification");
         }
	} 

  setcontrols();

});
JS;
$this->registerJs($js);
?>