<?php
use backend\models\business\ItemEntry;
use backend\models\business\ItemInvoiceForm;
use backend\models\business\Product;
use backend\models\business\SectorLocation;
use backend\models\nomenclators\TaxRateType;
use backend\models\nomenclators\TaxType;
use common\models\GlobalFunctions;
use kartik\number\NumberControl;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model \backend\models\business\ItemEntry */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="row">
	<div class="col-xs-12">
		<div class="box box-primary">
			<!-- /.box-header -->
			<div class="box-body">
		        <?php
		        $form = ActiveForm::begin([
		        'options' => ['enctype' => 'multipart/form-data'], 'id' => $model->formName(),
		                      'action' => $model->isNewRecord ? Url::to(['/item-entry/create_ajax'], GlobalFunctions::URLTYPE): Url::to(["/item-entry/update_ajax?id=".$model->id.""], GlobalFunctions::URLTYPE)
		        ]);?>
            	<?= Html::hiddenInput('entry_id', $model->entry_id); ?>
                <div class="row">
                    <div class="col-sm-4">
					<?=
						$form->field($model, "product_id")->widget(Select2::classname(), [
							"data" => ItemInvoiceForm::getProductSelectMap(true),
							"language" => Yii::$app->language,
							"options" => ["placeholder" => "----", "multiple" => false],
							"pluginOptions" => [
								"allowClear" => true
							],
						]);
						?>
						<?php
						Product::getSelectMap();
						/*
                        <?=
                        $form->field($model, "product_id")->widget(Select2::classname(), [
                            "data" => Product::getSelectMap(),
                            "language" => Yii::$app->language,
                            "options" => ["placeholder" => "----", "multiple"=>false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                        ]);
						*/
                        ?>
                    </div>
                    <div class="col-sm-2">
                        <?=
                        $form->field($model, "entry_quantity")->widget(NumberControl::classname(), [
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
                    <div class="col-sm-2">
                        <?=
                        $form->field($model, "price")->widget(NumberControl::classname(), [
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

                    <div class="col-sm-4">
                        <?=
                        $form->field($model, "sector_location_id")->widget(Select2::classname(), [
                            "data" => SectorLocation::getSelectMap(false,null,$model->entry->branch_office_id),
                            "language" => Yii::$app->language,
                            "options" => ["placeholder" => "Ubicación [Sector]", "multiple"=>false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                        ]);
                        ?>
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


					<?php
                    $labelbtn = $model->isNewRecord ? Yii::t('backend', 'Crear') : Yii::t('backend', 'Actualizar');
                    ?>
                    <div class="form-group pull-right">
                      <?= Html::a('<i class="glyphicon glyphicon-remove text-danger"></i> '.Yii::t('backend','Cancelar'), ['index'], ['data-pjax'=>0, 'class' => 'btn btn-default', 'id'=>'btn-cancel-item_entry']); ?>&nbsp;
                      <?= Html::submitButton("<i class=\"glyphicon glyphicon-floppy-save text-success\"></i> ".$labelbtn, ['class'=> 'btn btn-default', 'name'=>'btnguardar']); ?>
                    </div>  
			    <?php ActiveForm::end(); ?>
			</div>
		</div>
	</div>
</div>

<?php

$url_get_price = \yii\helpers\Url::to(['product/get-price'], GlobalFunctions::URLTYPE);
$js = <<<JS
// get the form id and set the event
$('form#{$model->formName()}').on('beforeSubmit', function(e) {
	var formdata = false;
	if(window.FormData){
		formdata = new FormData($(this)[0]);
	}

	var formAction = $(this).attr('action');
	$.ajax({
		type        : 'POST',
		url         : formAction,
		cache       : false,
		data        : formdata ? formdata : form.serialize(),
		contentType : false,
		processData : false,

		success: function(response) {
                var tax = parseFloat(response.total_tax); 
                var amount = parseFloat(response.amount); 
				$("#entry-total_tax-disp").val(tax.toFixed(2));
				$("#entry-total_tax").val(tax.toFixed(2));

				$("#entry-amount-disp").val(amount.toFixed(2));
				$("#entry-amount").val(amount.toFixed(2));

				$.pjax.reload({container: '#grid-item_entry-pjax', timeout: 2000});
				$.notify({
						"message": response.message,
						"icon": "glyphicon glyphicon-ok-sign",
						"title": response.titulo,						
						"showProgressbar": false,
						"url":"",						
						"target":"_blank"},{"type": response.type});
				showPanels();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			$.notify({
				"message": "Ha ocurrido un error. Inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema",
				"icon": "glyphicon glyphicon-remove text-danger-sign",
				"title": "Error <hr class='kv-alert-separator'>",			
				"showProgressbar": false,
				"url":"",						
				"target":"_blank"},{"type": "danger"}
			);
		}				
		
	});
    return false;
	// do whatever here, see the parameter \$form1? is a jQuery Element to your form
}).on('submit', function(e){
	e.preventDefault();
});
		
$("#btn-cancel-item_entry").click(function(e) {
	e.preventDefault();
	showPanels();
});			

function showPanels()
{
	$("#panel-form-item_entry").html('');
	$("#panel-grid-item_entry").show(15);	
	$.pjax.reload({container: '#grid-item_entry-pjax', timeout: 2000});			
}

$("#itementry-product_id").change(function() {
    var value_id = $('#itementry-product_id').val();
    
    $.ajax({
		type : 'GET',
		url : "$url_get_price?id="+value_id,

		success: function(response) {
             var change_value = response.quantity;
             $("#itementry-price-disp").val(change_value);
             $("#itementry-price").val(change_value);

			 $("#itementry-tax_type_id").val(response.tax_type_id).trigger("change");	
			 $("#itementry-tax_rate_type_id").val(response.tax_rate_type_id).trigger("change");		 
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			$.notify({
				"message": "Ha ocurrido un error. Inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema",
				"icon": "glyphicon glyphicon-remove text-danger-sign",
				"title": "Error <hr class='kv-alert-separator'>",			
				"showProgressbar": false,
				"url":"",						
				"target":"_blank"},{"type": "danger"}
			);
		}
	});
});
JS;
$this->registerJs($js);

$url_get_tax_rate_type_value = Url::to(['tax-rate-type/get_percent'], GlobalFunctions::URLTYPE);
$js_extra = <<<JS
$(document).ready(function(e) {
			
		$("#itementry-tax_rate_type_id").change(function() 
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
                        $("#itementry-tax_rate_percent-disp").val(percent);				
                        $("#itementry-tax_rate_percent").val(percent);
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
                $("#itementry-tax_rate_percent-disp").val(0);				
                $("#itementry-tax_rate_percent").val(0);
                $("#hfield_tax_rate_percent").val(0);
            }
		});
});
JS;

// Register action buttons js
$this->registerJs($js_extra);
?>
