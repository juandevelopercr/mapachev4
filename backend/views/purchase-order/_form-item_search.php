<?php

use backend\models\business\ItemPurchaseOrderForm;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\UtilsConstants;
use backend\models\settings\Setting;
use common\models\GlobalFunctions;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model \backend\models\business\ItemPurchaseOrderForm */
/* @var $form yii\widgets\ActiveForm */

$maxNumItem = ($invoice->invoice_type == UtilsConstants::PRE_INVOICE_TYPE_INVOICE) ? Setting::getLineNumInvoice() : Setting::getLineNumTicket();
?>
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-body">
                <div id="panel-formulario" style="display: <?= ($itemCount < $maxNumItem) ? 'block' : 'none' ?>">
                    <?php
                    $form = ActiveForm::begin([
                        'options' => ['enctype' => 'multipart/form-data'], 'id' => $model->formName(),
                        'action' => Url::to(["/item-purchase-order/create_ajax"], GlobalFunctions::URLTYPE)
                    ]); ?>
                    <?= $form->field($model, "purchase_order_id")->hiddenInput()->label(false) ?>
                    <input type="hidden" id="hmaxnumitem" value="<?= $maxNumItem ?>">
                    <input type="hidden" id="hnumitem" value="<?= $itemCount ?>">
                    <div class="row">
                        <div class="col-sm-3">
                            <?=
                            $form->field($model, "product_service")->widget(Select2::classname(), [
                                "data" => ItemPurchaseOrderForm::getSelectMap(true),
                                "language" => Yii::$app->language,
                                "options" => ["placeholder" => "----", "multiple" => false],
                                "pluginOptions" => [
                                    "allowClear" => true
                                ],
                            ]);
                            ?>
                            <?=
                            $form->field($model, "product_code")->hiddenInput()->label(false)
                            ?>
                        </div>
                        <div class="col-sm-2">
                            <?=
                            $form->field($model, "quantity")->textInput(['maxlength' => true])
                            ?>
                        </div>

                        <div class="col-sm-2">
                            <?=
                            $form->field($model, "unit_type_id")->widget(Select2::classname(), [
                                "data" => UnitType::getSelectMap(true, true),
                                "language" => Yii::$app->language,
                                "options" => ["placeholder" => "----", "multiple" => false],
                                "pluginOptions" => [
                                    "allowClear" => true
                                ],
                            ]);
                            ?>
                        </div>

                        <div class="col-sm-3">

                            <?= $form->field($model, 'price_type')->widget(DepDrop::classname(), [
                                'type' => DepDrop::TYPE_SELECT2,
                                'data' => ($model->product_service !== '') ? UtilsConstants::getPriceTypeSelectByProduct(null, false, $model->product_service) : array(),
                                'options' => ['placeholder' => "----"],
                                'select2Options' => ['pluginOptions' => ['allowClear' => true]],
                                'pluginOptions' => [
                                    'depends' => ['itempurchaseorderform-product_service'],
                                    'url' => Url::to(['/util/get_price_types_product'], GlobalFunctions::URLTYPE),
                                    'params' => ['input-type-1', 'input-type-2']
                                ]
                            ]);
                            ?>

                        </div>

                        <div class="col-sm-2">
                            <?= Html::submitButton("<i class=\"glyphicon glyphicon-floppy-save text-success\"></i> " . Yii::t('backend', 'Agregar'), ['class' => 'btn btn-default', 'style' => 'margin-top:24px;', 'name' => 'btnguardar']); ?>
                        </div>

                    </div>
                    <?php ActiveForm::end(); ?>
                </div>

                <div id="panel-informacion" style="display: <?= ($itemCount >= $maxNumItem) ? 'block' : 'none' ?>">

                    <br />
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-12">
                                <div style="margin-right:15px; float: left;">
                                    <span style="color: #000000;">
                                        <strong>Advertencia!</strong> Ha llegado al número máximo de items permitidos.
                                    </span>
                                </div>
                                <div style="margin-right:15px; float: left;">
                                    <a href="<?= Url::to(['/purchase-order/clone', 'id' => $invoice->id, 'noItem' => 1], GlobalFunctions::URLTYPE) ?>" class="btn btn-primary active">
                                        <i class="glyphicon glyphicon-glyphicon-repeat" aria-hidden="true"></i> Crear Nueva
                                    </a>
                                </div>

                                <div style="margin-right:15px; float: left;">
                                    <a href="<?= Url::to(['/purchase-order/index'], GlobalFunctions::URLTYPE) ?>" class="btn btn-danger">
                                        <i class="glyphicon glyphicon-glyphicon-repeat" aria-hidden="true"></i> Salir
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$url_product_get_code = Url::to(['/product/get-code'], GlobalFunctions::URLTYPE);
$url_service_get_code = Url::to(['/service/get-code'], GlobalFunctions::URLTYPE);
$url_product_get_info_by_code = Url::to(['/product/get_info_by_code'], GlobalFunctions::URLTYPE);
$default_price_type = UtilsConstants::CUSTOMER_ASSIGN_PRICE_1;
$js = <<<JS
$("#itempurchaseorderform-product_code").on("keyup keypress keydown", function(event) {
    var key_press = parseInt(event.which);
    if(key_press == 9) //press Tab
    {
        event.preventDefault();
        $("#itempurchaseorderform-quantity").focus().select();
        
        $.ajax({
            type: "GET",
            url : "$url_product_get_info_by_code?code="+$(this).val(),     
            success : function(response) 
            {
                $("#itempurchaseorderform-unit_type_id").val(response.unit_type_id).trigger("change");
                $("#itempurchaseorderform-price_type").val(response.price_type).trigger("change");
                if(!response.success)
                {
                    $.notify({
                    "message": "No existe ningún producto con el código seleccionado",
                    "icon": "glyphicon glyphicon-remove text-danger-sign",
                    "title": "Informaci&oacute;n <hr class=\"kv-alert-separator\">",						
                    "showProgressbar": false,
                    "url":"",						
                    "target":"_blank"},{"type": "danger"}
                    );
                }
           
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
				$("#itempurchaseorderform-product_code").val('');
				$("#itempurchaseorderform-product_code").focus();
				$("#itempurchaseorderform-quantity").val(1);
				$("#itempurchaseorderform-quantity-disp").val(1);
				$("#itempurchaseorderform-product_service").val('').trigger("change");
                $("#itempurchaseorderform-price_type").val('').trigger("change");
				$("#itempurchaseorderform-unit_type_id").val('').trigger("change");
				
                $("#hnumitem").val(response.itemCount);
                setVisibilidadFormulario();     

				$.pjax.reload({container: '#grid-item_purchaseorder-pjax', timeout: 2000});
				
				$.notify({
						"message": response.message,
						"icon": "glyphicon glyphicon-ok-sign",
						"title": response.titulo,						
						"showProgressbar": false,
						"url":"",						
						"target":"_blank"},{"type": response.type});
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

$("#itempurchaseorderform-product_service").change(function() {
    var target_value = $(this).val();
    var split = target_value.split('-');
    var id_value = split[1];
    var default_price_type = '';
    
    if(id_value != '' && id_value != null)
    {
        if(split[0] == 'P')
        {
          url_target = "$url_product_get_code?id="+id_value;  
          $("#itempurchaseorderform-price_type").val("$default_price_type").trigger("change");
        }
        else 
        {
            url_target = "$url_service_get_code?id="+id_value;  
        }
        
        $.ajax({
            type: "GET",
            url : url_target,     
            success : function(response) 
            {
                 $("#itempurchaseorderform-product_code").val(response.code);
                $("#itempurchaseorderform-unit_type_id").val(response.unit_type_id).trigger("change");
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
        $("#itempurchaseorderform-product_code").val('');
    }
});


function setVisibilidadFormulario(){
    if (parseInt($("#hnumitem").val()) >= parseInt($("#hmaxnumitem").val())){
        $("#panel-informacion").show();
        $("#panel-formulario").hide();
    }
    else
    {
        $("#panel-informacion").hide();
        $("#panel-formulario").show();
    }
}

setVisibilidadFormulario();


JS;
$this->registerJs($js);
?>