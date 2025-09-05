<?php

use backend\models\business\ItemPaymetOrderForm;
use backend\models\nomenclators\UnitType;
use common\models\GlobalFunctions;
use kartik\number\NumberControl;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;


/* @var $this yii\web\View */
/* @var $model \backend\models\business\ItemPaymetOrderForm */
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
                    'action' => Url::to(["/item-payment-order/create_ajax"], GlobalFunctions::URLTYPE)
                ]); ?>
                <?= $form->field($model, "payment_order_id")->hiddenInput()->label(false) ?>
                <div class="row">
                    <div class="col-sm-2">
                        <?=
                        $form->field($model, "product_code")->textInput(['maxlength' => true, 'autofocus' => 'autofocus'])
                        ?>
                    </div>
                    <div class="col-sm-4">
                        <?=
                        $form->field($model, "product_service")->widget(Select2::classname(), [
                            "data" => ItemPaymetOrderForm::getSelectMap(true, false),
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
                            "data" => UnitType::getSelectMap(true, true),
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
						$form->field($model, "price_unit")->widget(NumberControl::classname(), [
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
                </div>
                <div class="box-footer">

                    <div class="col-sm-2" style="padding: 0;">
                        <?= Html::submitButton("<i class=\"glyphicon glyphicon-floppy-save text-success\"></i> ".Yii::t('backend', 'Agregar'), ['class'=> 'btn btn-default','style'=>'margin-top:24px;', 'name'=>'btnguardar']); ?>
                    </div>

                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<?php
$url_get_code = Url::to(['/product/get-code'], GlobalFunctions::URLTYPE);
$js = <<<JS
$("#itempaymetorderform-product_code").on("keyup keypress keydown", function(event) {
    var key_press = parseInt(event.which);
    if(key_press == 9) //press Tab
    {
        event.preventDefault();
        $("#itempaymetorderform-quantity").focus().select();
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
				$("#itempaymetorderform-product_code").val('');
				$("#itempaymetorderform-product_code").focus();
				$("#itempaymetorderform-quantity").val(1);
				$("#itempaymetorderform-quantity-disp").val(1);
				$("#itempaymetorderform-product_service").val('').trigger("change");
				
				$.pjax.reload({container: '#grid-item_payment_order-pjax', timeout: 2000}).done(function(){
                    $.pjax.reload({container:"#grid-reception_item_po-pjax"});
                });
				
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

$("#itempaymetorderform-product_service").change(function() {
    var target_value = $(this).val();
    var split = target_value.split('-');
    var id_value = split[1];
    
    if(id_value != '' && id_value != null)
    {
        $.ajax({
            type: "GET",
            url : "$url_get_code?id="+id_value,     
            success : function(response) 
            {
                $("#itempaymetorderform-product_code").val(response.code);
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
        $("#itempaymetorderform-product_code").val('');
    }
});
JS;
$this->registerJs($js);
?>