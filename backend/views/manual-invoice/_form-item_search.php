<?php

use backend\models\business\ItemManualInvoiceForm;
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
/* @var $model \backend\models\business\ItemManualInvoiceForm */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-body">
                <div id="panel-formulario">
                    <?php
                    $form = ActiveForm::begin([
                        'options' => ['enctype' => 'multipart/form-data'], 'id' => $model->formName(),
                        'action' => Url::to(["/item-manual-invoice/create_ajax"], GlobalFunctions::URLTYPE)
                    ]); ?>
                    <?= $form->field($model, "invoice_id")->hiddenInput()->label(false) ?>
                    <div class="row">
                        <div class="col-sm-3">
                            <?=
                            $form->field($model, "service")->widget(Select2::classname(), [
                                "data" => ItemManualInvoiceForm::getSelectMap(true),
                                "language" => Yii::$app->language,
                                "options" => ["placeholder" => "----", "multiple" => false],
                                "pluginOptions" => [
                                    "allowClear" => true
                                ],
                            ]);
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
                                "data" => UnitType::getSelectMapProfessionalService(true, true),
                                "language" => Yii::$app->language,
                                "options" => ["placeholder" => "----", "multiple" => false],
                                "pluginOptions" => [
                                    "allowClear" => true
                                ],
                            ]);
                            ?>
                        </div>

                        <div class="col-sm-3">
                            <?= $form->field($model, "price")->textInput(['maxlength' => true]) ?>
                        </div>

                        <div class="col-sm-2">
                            <?= Html::submitButton("<i class=\"glyphicon glyphicon-floppy-save text-success\"></i> " . Yii::t('backend', 'Agregar'), ['class' => 'btn btn-default', 'style' => 'margin-top:24px;', 'name' => 'btnguardar']); ?>
                        </div>

                    </div>
                </div>

                <?php ActiveForm::end(); ?>

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
				$("#itemmanualinvoiceform-quantity").val(1);
				$("#itemmanualinvoiceform-quantity-disp").val(1);
				$("#itemmanualinvoiceform-service").val('').trigger("change");
                $("#itemmanualinvoiceform-price").val('').trigger("change");
				$("#itemmanualinvoiceform-unit_type_id").val('').trigger("change");

				
				$.pjax.reload({container: '#grid-item_manual_invoice-pjax', timeout: 2000});
				
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
				"message": "Ha ocurrido un error. Int茅ntelo nuevamente, si el error persiste, p贸ngase en contacto con el administrador del sistema",
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

$("#itemmanualinvoiceform-service").change(function() {
    var target_value = $(this).val();
    var split = target_value.split('-');
    var id_value = split[1];
    var default_price_type = '';
    
    if(id_value != '' && id_value != null)
    {
        url_target = "$url_service_get_code?id="+id_value;  
        
        $.ajax({
            type: "GET",
            url : url_target,     
            success : function(response) 
            {
               // $("#itemmanualinvoiceform-service-product_code").val(response.code);
                $("#itemmanualinvoiceform-unit_type_id").val(response.unit_type_id).trigger("change");
                $("#itemmanualinvoiceform-price").val(response.price).trigger("change");
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) 
            {
                $.notify({
                    "message": "Ha ocurrido un error. Int茅ntelo nuevamente, si el error persiste, p贸ngase en contacto con el administrador del sistema",
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
       // $("#itemmanualinvoiceform-service-product_code").val('');
    }
});

JS;
$this->registerJs($js);
?>