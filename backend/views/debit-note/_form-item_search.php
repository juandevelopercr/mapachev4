<?php

use backend\models\business\ItemDebitNoteForm;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\UtilsConstants;
use common\models\GlobalFunctions;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model \backend\models\business\ItemDebitNoteForm */
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
		                      'action' => Url::to(["/item-debit-note/create_ajax"], GlobalFunctions::URLTYPE)
		        ]);?>
            	<?= $form->field($model, "debit_note_id")->hiddenInput()->label(false) ?>
                <div class="row">
                    <div class="col-sm-3">
                        <?=
                        $form->field($model, "product_service")->widget(Select2::classname(), [
                            "data" => ItemDebitNoteForm::getSelectMap(true),
                            "language" => Yii::$app->language,
                            "options" => ["placeholder" => "----", "multiple"=>false],
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
                            "data" => UnitType::getSelectMap(true,true),
                            "language" => Yii::$app->language,
                            "options" => ["placeholder" => "----", "multiple"=>false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                        ]);
                        ?>
                    </div>

                    <div class="col-sm-3">

                        <?= $form->field($model, 'price_type')->widget(DepDrop::classname(), [
                            'type'=>DepDrop::TYPE_SELECT2,
                            'data'=>($model->product_service !== '') ? UtilsConstants::getPriceTypeSelectByProduct(null,false, $model->product_service): array(),
                            'options'=>['placeholder'=> "----"],
                            'select2Options'=>['pluginOptions'=>['allowClear'=>true]],
                            'pluginOptions'=>[
                                'depends'=>['itemdebitnoteform-product_service'],
                                'url'=>Url::to(['/util/get_price_types_product'], GlobalFunctions::URLTYPE),
                                'params'=>['input-type-1', 'input-type-2']
                            ]
                        ]);
                        ?>

                    </div>

                    <div class="col-sm-2">
                        <?= Html::submitButton("<i class=\"glyphicon glyphicon-floppy-save text-success\"></i> ".Yii::t('backend', 'Agregar'), ['class'=> 'btn btn-default','style'=>'margin-top:24px;', 'name'=>'btnguardar']); ?>
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
				$("#itemdebitnoteform-product_code").val('');
				$("#itemdebitnoteform-product_code").focus();
				$("#itemdebitnoteform-quantity").val(1);
				$("#itemdebitnoteform-quantity-disp").val(1);
				$("#itemdebitnoteform-product_service").val('').trigger("change");
                $("#itemdebitnoteform-price_type").val('').trigger("change");
				$("#itemdebitnoteform-unit_type_id").val('').trigger("change");
				
				$.pjax.reload({container: '#grid_debitnote-pjax', timeout: 2000});
				
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

$("#itemdebitnoteform-product_service").change(function() {
    var target_value = $(this).val();
    var split = target_value.split('-');
    var id_value = split[1];
    var default_price_type = '';
    
    if(id_value != '' && id_value != null)
    {
        if(split[0] == 'P')
        {
          url_target = "$url_product_get_code?id="+id_value;  
          $("#itemdebitnoteform-price_type").val("$default_price_type").trigger("change");
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
                $("#itemdebitnoteform-product_code").val(response.code);
                $("#itemdebitnoteform-unit_type_id").val(response.unit_type_id).trigger("change");
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
        $("#itemdebitnoteform-product_code").val('');
    }
});
JS;
$this->registerJs($js);
?>
