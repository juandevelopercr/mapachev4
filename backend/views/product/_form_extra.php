<?php

use backend\models\nomenclators\ExonerationDocumentType;
use backend\models\nomenclators\TaxRateType;
use backend\models\nomenclators\TaxType;
use common\models\GlobalFunctions;
use kartik\datecontrol\DateControl;
use kartik\number\NumberControl;
use kartik\select2\Select2;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $model backend\models\business\Product */
/* @var $form yii\widgets\ActiveForm */
?>

    <div class="row">
        <div class="col-md-12">
            <fieldset id="pedido_minimo_fieldset" style="width: 100%; border: 1px solid #C0C0C0;">
                <legend style="width: auto; margin: 8px; border: 0; padding-right: 1%; padding-left: 1%; font-size: 16px; font-weight: bold; border: 1px solid #C0C0C0;"><?= Yii::t('backend','Descuento') ?></legend>
                <div class="col-md-3">
                    <?=
                    $form->field($model, "discount_amount")->widget(NumberControl::classname(), [
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
                <div class="col-md-9">
                    <?= $form->field($model, 'nature_discount')->textInput(['maxlength' => true]) ?>
                </div>
            </fieldset>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <fieldset id="pedido_minimo_fieldset" style="width: 100%; border: 1px solid #C0C0C0;">
                <legend style="width: auto; margin: 8px; border: 0; padding-right: 1%; padding-left: 1%; font-size: 16px; font-weight: bold; border: 1px solid #C0C0C0;"><?= Yii::t('backend','Impuesto') ?></legend>
                <div class="col-md-12">
                    <?=
                    $form->field($model, "tax_type_id")->widget(Select2::classname(), [
                        "data" => TaxType::getSelectMap(),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple"=>false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ]);
                    ?>
                </div>
                <div class="col-md-12">
                    <?=
                    $form->field($model, "tax_rate_type_id")->widget(Select2::classname(), [
                        "data" => TaxRateType::getSelectMap(),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple"=>false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ]);
                    ?>
                </div>
                <div class="col-md-4">
                    <?=
                    $form->field($model, "tax_rate_percent")->widget(NumberControl::classname(), [
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 2,
                            "suffix" => ' %',                            
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"],
                        "readonly"=>true,
                    ])
                    ?>
                </div>
            </fieldset>
        </div>

        <div class="col-md-6" id="col-exonerado" style="display:block">
            <fieldset id="pedido_minimo_fieldset" style="width: 100%; border: 1px solid #C0C0C0;">
                <legend style="width: auto; margin: 8px; border: 0; padding-right: 1%; padding-left: 1%; font-size: 16px; font-weight: bold; border: 1px solid #C0C0C0;"><?= Yii::t('backend', 'Exoneración') ?></legend>

                <div class="col-md-12">
                    <?=
                    $form->field($model, "exoneration_document_type_id")->widget(Select2::classname(), [
                        "data" => ExonerationDocumentType::getSelectMap(),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple"=>false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ]);
                    ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'name_institution_exoneration')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'number_exoneration_doc')->textInput(['maxlength' => true]) ?>
                </div>

                <div class="col-md-6">
                    <?=
                    $form->field($model, "exoneration_date")->widget(DateControl::classname(), [
                        "type" => DateControl::FORMAT_DATE
                    ])
                    ?>
                </div>
                <div class="col-md-4">
                    <?=
                    $form->field($model, "exoneration_purchase_percent")->widget(NumberControl::classname(), [
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 2,
                            "suffix" => ' %'
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>
                </div>

            </fieldset>
        </div>
    </div>

    <input type="hidden" name="hfield_tax_rate_percent" id="hfield_tax_rate_percent" value="<?= !is_null($model->id) ? $model->tax_rate_percent: 0 ?>"/>                  


<?php
$url_get_tax_rate_type_value = Url::to(['tax-rate-type/get_percent'], GlobalFunctions::URLTYPE);

$js_extra = <<<JS
$(document).ready(function(e) {
    		
		$("#product-tax_rate_type_id").change(function() 
		{
		    var id_value = $(this).val();
		    if(id_value != '' && id_value != null)
            {
                $.ajax({
                    type: "GET",
                    url : "$url_get_tax_rate_type_value?id="+id_value,     
                    success : function(response) 
                    {
                        var percent = response.percent;
                        $("#product-tax_rate_percent-disp").val(percent);				
                        $("#product-tax_rate_percent").val(percent);
                        $("#hfield_tax_rate_percent").val(percent);
                 
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) 
                    {
                        $.notify({
                            "message": "Ha ocurrido un error. Inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema",
                            "icon": "glyphicon glyphicon-remove text-danger-sign",
                            "title": "Informaci&oacute;n <hr class=\"kv-alert-separator\">",						
                            "showProgressbar": false,
                            "url":"",						
                            "target":"_blank"},{"type": "danger"}
                        );
                    }				
			    });
            }
            else 
            {
                $("#product-tax_rate_percent-disp").val(0);				
                $("#product-tax_rate_percent").val(0);
                $("#hfield_tax_rate_percent").val(0);
            }
		});
});
JS;

// Register action buttons js
$this->registerJs($js_extra);
?>