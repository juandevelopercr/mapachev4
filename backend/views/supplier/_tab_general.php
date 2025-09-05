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
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\ConditionSale;
use backend\models\settings\Issuer;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Supplier */
/* @var $form yii\widgets\ActiveForm */
?>

    <div class="box-body">
        <?php
        $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

        <input type="hidden" name="change_usd" id="change_usd" value="<?= Issuer::getChange_type_dollar() ?>"/>

        <div class="row">
            <div class="col-md-2">
                <?= $form->field($model, 'code')->textInput(['maxlength' => true,'readonly'=>true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

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
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'web_site')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-3">
                <?=
                $form->field($model, "entry_date")->widget(DateControl::classname(), [
                    "type" => DateControl::FORMAT_DATE
                ])
                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?=
                $form->field($model, "condition_sale_id")->widget(Select2::classname(), [
                    "data" => ConditionSale::getSelectMap(),
                    "language" => Yii::$app->language,
                    "options" => ["placeholder" => "----", "multiple"=>false],
                    "pluginOptions" => [
                        "allowClear" => true
                    ],
                ]);
                ?>
            </div>
            <div class="col-md-2">
                <?=
                $form->field($model, "colon_credit")->widget(NumberControl::classname(), [
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
            <div class="col-md-2">
                <?=
                $form->field($model, "dollar_credit")->widget(NumberControl::classname(), [
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
            <div class="col-md-2">
                <?=
                $form->field($model, "credit_days_id")->widget(Select2::classname(), [
                    "data" => CreditDays::getSelectMap(),
                    "language" => Yii::$app->language,
                    "options" => [
                        "placeholder" => "----",
                        "multiple"=>false

                    ],
                    "pluginOptions" => [
                        "allowClear" => true
                    ],
                ]);
                ?>
            </div>
            <div class="col-md-2">
                <?=
                $form->field($model,"max_credit")->widget(SwitchInput::classname(), [
                    "type" => SwitchInput::CHECKBOX,
                    "pluginOptions" => [
                        "onText"=> Yii::t("backend","SI"),
                        "offText"=> Yii::t("backend","NO")
                    ]
                ])
                ?>
            </div>
        </div>

    </div>
    <div class="box-footer">
        <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> '.Yii::t('backend','Crear') : '<i class="fa fa-pencil"></i> '.Yii::t('yii', 'Update'), ['class' => 'btn btn-default btn-flat']) ?>
        <?php
            $return_cancel = (isset($return_import) && $return_import)? '/entry/import_xml' : 'index';
        ?>
        <?= Html::a('<i class="fa fa-remove"></i> '.Yii::t('backend','Cancelar'),[$return_cancel], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Cancelar')]) ?>
    </div>
<?php ActiveForm::end(); ?>

<?php
$id_credit = ConditionSale::getIdCreditConditionSale();
$js = <<<JS
// get the form id and set the event
$(document).ready(function(e) {
   
  setcontrols();
    
  $("#supplier-condition_sale_id").change(function() {
	setcontrols();
  });

  function setcontrols()
  {		
 	 if ($("#supplier-condition_sale_id").val() == "$id_credit")
     {
        $("#supplier-colon_credit-disp").attr('disabled', false);
		$("#supplier-dollar_credit-disp").attr('disabled', false);	
		$("#supplier-credit_days_id").attr('disabled', false);
     }
	 else
	 {
        $("#supplier-colon_credit-disp").attr('disabled', true);
		$("#supplier-colon_credit-disp").val(0);
		$("#supplier-colon_credit").val(0);

		$("#supplier-dollar_credit-disp").attr('disabled', true);
		$("#supplier-dollar_credit-disp").val(0);
		$("#supplier-dollar_credit").val(0);
		
		$("#supplier-credit_days_id").attr('disabled', true);			
	 }
  }

	$("#supplier-colon_credit-disp").keyup(function (e) {
		refresh_prices('usd');
	});   	
	
	$("#supplier-dollar_credit-disp").keyup(function (e) {
		refresh_prices('colon');
	});   		
	
	function refresh_prices(type)
	{		
	    var change_type = $("#change_usd").val();
	    
		if (type == 'usd')
		{			
			price = $("#supplier-colon_credit-disp").val().replace(/\./g,"");
		    price = price.replace(",",".");
		    change_value = price / change_type;
			
			$("#supplier-dollar_credit-disp").val(change_value.toFixed(2));
			$("#supplier-dollar_credit").val(change_value.toFixed(2));
		}
		else
		{
			price = $("#supplier-dollar_credit-disp").val().replace(/\./g,"");
		    price = price.replace(",",".");
			change_value = price * change_type;
			$("#supplier-colon_credit-disp").val(change_value.toFixed(2));
			$("#supplier-colon_credit").val(change_value.toFixed(2));
		}	
	}

});
JS;
$this->registerJs($js);
?>
