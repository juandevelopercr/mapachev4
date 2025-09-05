<?php

use backend\models\nomenclators\Cabys;
use backend\models\nomenclators\ExonerationDocumentType;
use backend\models\nomenclators\TaxRateType;
use backend\models\nomenclators\TaxType;
use backend\models\nomenclators\UnitType;
use common\models\GlobalFunctions;
use kartik\datecontrol\DateControl;
use kartik\form\ActiveForm;
use kartik\number\NumberControl;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;


/* @var $this yii\web\View */
/* @var $model backend\models\business\Service */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box-body">
<?php

 $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
        
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'code')->textInput(['maxlength' => true,'readonly'=>true]) ?>
        </div>
        <div class="col-md-10">
            <?php
            // The controller action that will render the list
            $url_cabys = Url::to(['cabys/cabys_list'], GlobalFunctions::URLTYPE);

            // Get the initial values
            $init_value_cabys = empty($model->cabys_id) ? '' : Cabys::getLabelSelectById($model->cabys_id);

            echo $form->field($model, 'cabys_id')->widget(Select2::classname(), [
                'initValueText' => $init_value_cabys, // set the initial display text
                "language" => Yii::$app->language,
                "options" => ["placeholder" => "----", "multiple"=>false],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 2,
                    'language' => [
                        'errorLoading' => new JsExpression("function () { return 'Buscando resultados...'; }"),
                    ],
                    'ajax' => [
                        'url' => $url_cabys,
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(cabys) { return cabys.text; }'),
                    'templateSelection' => new JsExpression('function (cabys) { return cabys.text; }'),
                ],
            ]);
            ?>
        </div>

    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-md-3">
            <?=
            $form->field($model, "price")->widget(NumberControl::classname(), [
                "maskedInputOptions" => [
                    "allowMinus" => false,
                    "groupSeparator" => ".",
                    "radixPoint" => ",",
                    "digits" => 5,
                ],
                "displayOptions" => ["class" => "form-control kv-monospace"],
                "saveInputContainer" => ["class" => "kv-saved-cont"]
            ])
            ?>
        </div>

        <div class="col-md-3">
            <?=
            $form->field($model, "unit_type_id")->widget(Select2::classname(), [
                "data" => UnitType::getSelectMapProfessionalService(),
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
            <fieldset id="pedido_minimo_fieldset" style="width: 100%; border: 1px solid #C0C0C0;">
                <legend style="width: auto; margin: 8px; border: 0; padding-right: 1%; padding-left: 1%; font-size: 16px; font-weight: bold; border: 1px solid #C0C0C0;"><?= Yii::t('backend','Descuento') ?></legend>
                <div class="col-md-3">
                    <?=
                    $form->field($model, "discount_amount")->widget(NumberControl::classname(), [
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 5,
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
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>
                </div>
                <div class="col-md-5">
                    <?=
                    $form->field($model, "tax_amount")->widget(NumberControl::classname(), [
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 5,
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
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
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>
                </div>
                <div class="col-md-5">
                    <?=
                    $form->field($model, "exonerated_tax_amount")->widget(NumberControl::classname(), [
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 5,
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
    <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> '.Yii::t('backend','Crear') : '<i class="fa fa-pencil"></i> '.Yii::t('yii', 'Update'), ['class' => 'btn btn-default btn-flat']) ?>
    <?= Html::a('<i class="fa fa-remove"></i> '.Yii::t('backend','Cancelar'),['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Cancelar')]) ?>
</div>
<?php ActiveForm::end(); ?>

<?php
$url_get_tax_rate_type_value = Url::to(['tax-rate-type/get_percent'], GlobalFunctions::URLTYPE);
$url_get_cabys_value = Url::to(['cabys/get_percent'], GlobalFunctions::URLTYPE);
$id_generic_tax_type = TaxType::getIdTaxGeneric();

$js = <<<JS
$(document).ready(function(e) {
               
		$("#service-price-disp").keyup(function(e) {
			refreshValues();
        });
        		
		$("#service-discount_amount-disp").keyup(function(e) {
			refreshValues();
        });	
		
		$("#service-tax_rate_percent-disp").keyup(function(e) {
			refreshValues();
        });		
		
		$("#service-exoneration_purchase_percent-disp").keyup(function(e) {
			refreshValues();
        });	
		
		function refreshValues()
		{
		    price = $("#service-price-disp").val().replace(/\./g,"");
		    price = price.replace(",",".");
		    
            discount_amount = $("#service-discount_amount-disp").val().replace(/\./g,"");
		    discount_amount = discount_amount.replace(",",".");  
		              
            tax_rate_percent = $("#service-tax_rate_percent-disp").val().replace(/\./g,"");
		    tax_rate_percent = tax_rate_percent.replace(",",".");            
		    
		    exoneration_purchase_percent = $("#service-exoneration_purchase_percent-disp").val().replace(/\./g,"");
		    exoneration_purchase_percent = exoneration_purchase_percent.replace(",",".");
		    		    
		    var tax = 0;
		    
		    if (tax_rate_percent >= 0 && exoneration_purchase_percent >= 0)
			{
				tax = (price - discount_amount) * tax_rate_percent / 100;			
				exonerated_tax_amount = (tax * exoneration_purchase_percent) / 100;   			
			}
			else
            {
                exonerated_tax_amount = 0;
            }
				
            $("#service-tax_amount-disp").val(tax.toFixed(2));	
            $("#service-tax_amount").val(tax.toFixed(2));
            	
			$("#service-exonerated_tax_amount-disp").val(exonerated_tax_amount.toFixed(2));	
			$("#service-exonerated_tax_amount").val(exonerated_tax_amount.toFixed(2));	
		}
		
		refreshValues();
		
		$("#service-tax_rate_type_id").change(function() 
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
                        $("#service-tax_rate_percent-disp").val(percent);				
                        $("#service-tax_rate_percent").val(percent);
                        refreshValues();
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
                $("#service-tax_rate_percent-disp").val(0);				
                $("#service-tax_rate_percent").val(0);
                
                $("#service-tax_amount-disp").val(0);	
                $("#service-tax_amount").val(0);
            }
		});
		
		$("#service-cabys_id").change(function() 
		{
		    var id_value = $(this).val();
		    if(id_value != '' && id_value != null)
            {
                $.ajax({
                    type: "GET",
                    url : "$url_get_cabys_value?id="+id_value,     
                    success : function(response) 
                    {
                        var percent = response.percent;
                        $("#service-tax_rate_percent-disp").val(percent);				
                        $("#service-tax_rate_percent").val(percent);
                        
                        $("#service-tax_type_id").val("$id_generic_tax_type").trigger("change");	
                        //$("#service-tax_rate_type_id").val(id_impuesto_tarifa).trigger("change");	
                        refreshValues();
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
		});
});
JS;

// Register action buttons js
$this->registerJs($js);
?>
