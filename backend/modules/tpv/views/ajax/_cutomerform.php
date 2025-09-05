<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\switchinput\SwitchInput;
use kartik\number\NumberControl;
use common\models\GlobalFunctions;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\Boxes;
use backend\models\business\Customer;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use backend\models\nomenclators\IdentificationType;
use backend\models\nomenclators\Province;
use backend\models\nomenclators\Canton;
use yii\web\JsExpression;
use backend\models\nomenclators\Disctrict;
use common\models\User;
use backend\models\nomenclators\PaymentMethod;
use yii\helpers\Url;
use kartik\depdrop\DepDrop;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Invoice */
/* @var $form yii\widgets\ActiveForm */
/* @var $searchModelItems \backend\models\business\ItemInvoiceSearch */
/* @var $dataProviderItems yii\data\ActiveDataProvider */
?>
<div class="modal-body">

	<?php
	$form = ActiveForm::begin([
		'options' => ['enctype' => 'multipart/form-data'], 'id' => $modelBuscador->formName(),
		'action' => Url::to(['/tpv/ajax/seleccionarcustomer'], GlobalFunctions::URLTYPE)
	]); ?>
	<div class="row">
		<div class="col-md-8">
			<?php
			// The controller action that will render the list
			$url_cabys = Url::to(['/tpv/ajax/searchcustomer'], GlobalFunctions::URLTYPE);

			// Get the initial values
			//$init_value_cabys = empty($model->cabys_id) ? '' : Cabys::getLabelSelectById($model->cabys_id);

			echo $form->field($modelBuscador, 'customer_id')->widget(Select2::classname(), [
				'initValueText' => '',
				"language" => Yii::$app->language,
				"options" => ["placeholder" => "----", "multiple" => false],
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
					'templateResult' => new JsExpression('function(customer) { return customer.text; }'),
					'templateSelection' => new JsExpression('function (customer) { return customer.text; }'),
				],
			]);
			?>
		</div>
		<div class="col-md-4" style="margin-top: 25px;">
			<?= Html::submitButton("<i class='glyphicon glyphicon-check text-success'></i> Seleccionar Cliente", ['class' => 'btn btn-default', 'name' => 'btnseleccionar']); ?>
		</div>
	</div>
	<?php ActiveForm::end(); ?>

	<div class="row ln-s"></div>	

	<?php
	$form = ActiveForm::begin([
		'options' => ['enctype' => 'multipart/form-data'], 'id' => $model->formName(),
		'action' => Url::to(['/tpv/ajax/addnewcustomer'], GlobalFunctions::URLTYPE)
	]); ?>
	<?= $form->field($model, 'customer_type_id')->hiddenInput()->label(false) ?>
	<?= $form->field($model, 'customer_classification_id')->hiddenInput()->label(false) ?>
	<?= $form->field($model, 'price_assigned')->hiddenInput()->label(false) ?>
	<?= $form->field($model, 'route_transport_id')->hiddenInput()->label(false) ?>
	<?php 
	/*
	<?= $form->field($model, 'collector_id')->hiddenInput()->label(false) ?>
	<?= $form->field($model, 'collector_id')->hiddenInput()->label(false) ?>
	*/
	?>
	<?= $form->field($model, 'condition_sale_id')->hiddenInput()->label(false) ?>
	<?= $form->field($model, 'pre_invoice_type')->hiddenInput()->label(false) ?>
	<div class="row">
		<div class="col-md-6">
			<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
		</div>
		<div class="col-md-6">
			<?=
			$form->field($model, "identification_type_id")->widget(Select2::classname(), [
				"data" => IdentificationType::getSelectMapCustomerAdd(),
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
		<div class="col-md-6">
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
		<div class="col-md-6">
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
			<?=
			$form->field($model, "province_id")->widget(Select2::classname(), [
				"data" => Province::getSelectMap(),
				"language" => Yii::$app->language,
				"options" => ["placeholder" => "----", "multiple" => false],
				"pluginOptions" => [
					"allowClear" => true
				],
			]);
			?>
		</div>

		<div class="col-md-6">
			<?= $form->field($model, 'canton_id')->widget(DepDrop::classname(), [
				'type' => DepDrop::TYPE_SELECT2,
				'data' => ($model->province_id > 0) ? Canton::getSelectMapSpecific($model->province_id) : array(),
				'options' => ['placeholder' => "----"],
				'select2Options' => ['pluginOptions' => ['allowClear' => true]],
				'pluginOptions' => [
					'depends' => ['customer-province_id'],
					'url' => Url::to(['/util/get_cantons'], GlobalFunctions::URLTYPE),
					'params' => ['input-type-1', 'input-type-2']
				]
			]);
			?>
		</div>
	</div>
	<div class="row">
		<div class="col-md-6">
			<?= $form->field($model, 'disctrict_id')->widget(DepDrop::classname(), [
				'type' => DepDrop::TYPE_SELECT2,
				'data' => ($model->canton_id > 0) ? Disctrict::getSelectMapSpecific($model->canton_id) : array(),
				'options' => ['placeholder' => "----"],
				'select2Options' => ['pluginOptions' => ['allowClear' => true]],
				'pluginOptions' => [
					'depends' => ['customer-province_id', 'customer-canton_id'],
					'url' => Url::to(['/util/get_dictrict'], GlobalFunctions::URLTYPE),
					'params' => ['input-type-1', 'input-type-2']
				]
			]);
			?>
		</div>
		<div class="col-md-6">
			<?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
		</div>
	</div>
	<div class="form-group pull-right" style="margin-top:15px;">
		<button type="button" class="btn btn-default glyphicon glyphicon-remove text-danger" data-dismiss="modal">Cancelar</button>
		&nbsp;
		<?= Html::submitButton("<i class='glyphicon glyphicon-floppy-save text-success'></i> Guardar", ['class' => 'btn btn-default', 'name' => 'btnguardar']); ?>
	</div>
	<br /> 
	<br /> 
	<?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<JS
// get the form id and set the event
function init()
{
	$('form#{$model->formName()}').on('beforeSubmit', function(e) {
		e.preventDefault();
		e.stopImmediatePropagation();	
		var formdata = false;
		if(window.FormData){
			formdata = new FormData($(this)[0]);
		}
		
		var formAction = $(this).attr('action');
		$("#spinner").show();
		$.ajax({
			type        : 'POST',
			url         : formAction,
			cache       : false,
			data        : formdata ? formdata : form.serialize(),
			contentType : false,
			processData : false,
	
			success: function(response) {
					if (response.result == true)
					{
						$.notify({
							"message": "Se ha seleccionado el cliente: " + response.customer_name,
							"icon": "glyphicon glyphicon-ok-sign",
							"title": 'Información',						
							"showProgressbar": false,
							"url":"",						
							"target":"_blank"},{"type": 'warning'});

							$('#orderCustomerId').val(response.customer_id);
							$('#orderCustomerName').val(response.customer_name);
							$('#orderCustomer').html(response.customer_name);
							$('#orderCustomer2').html(response.customer_name);

							//mmd_closed();
							$("#myModal2").modal('hide');
							$('form#{$model->formName()}').trigger("reset");
							mmd_closed();									
					}
					else
					{
						$.notify({
							"message": response.msg,
							"icon": "glyphicon glyphicon-ok-sign",
							"title": 'Información',						
							"showProgressbar": false,
							"url":"",						
							"target":"_blank"},{"type": 'danger'});
					}
					$("#spinner").hide();
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
				$("#spinner").hide();
			}				
		});
		return false;
		// do whatever here, see the parameter \$form1? is a jQuery Element to your form
	}).on('submit', function(e){
		e.preventDefault();
		e.stopImmediatePropagation();	
	});


	$('form#{$modelBuscador->formName()}').on('beforeSubmit', function(e) {
		e.preventDefault();
		e.stopImmediatePropagation();	
		var formdata = false;
		if(window.FormData){
			formdata = new FormData($(this)[0]);
		}
		
		var formAction = $(this).attr('action');
		$("#spinner").show();
		$.ajax({
			type        : 'POST',
			url         : formAction,
			cache       : false,
			data        : formdata ? formdata : form.serialize(),
			contentType : false,
			processData : false,
	
			success: function(response) {
					if (response.customer_id > 0)
					{
						$('#orderCustomerId').val(response.customer_id);
						$('#orderCustomerName').val(response.customer_name);
						$('#orderCustomer').text(response.customer_name);
						$('#orderCustomer2').text(response.customer_name);
						$.notify({
							"message": "Se ha seleccionado el cliente: " + response.customer_name,
							"icon": "glyphicon glyphicon-ok-sign",
							"title": 'Información',						
							"showProgressbar": false,
							"url":"",						
							"target":"_blank"},{"type": 'warning'});
						mmd_closed();				
					}
					else
					{
						msg = "Ese nombre no esta registrado.";
						$('#orderCustomerId').val('');
						$('#orderCustomerName').val('');
						$('#orderCustomer').text('');
						$('#orderCustomer2').text('');	
						$.notify({
							"message": msg,
							"icon": "glyphicon glyphicon-ok-sign",
							"title": 'Información',						
							"showProgressbar": false,
							"url":"",						
							"target":"_blank"},{"type": 'warning'});										
					}
					$("#spinner").hide();
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
				$("#spinner").hide();
			}				
		});
		return false;
		// do whatever here, see the parameter \$form1? is a jQuery Element to your form
	}).on('submit', function(e){
		e.preventDefault();
		e.stopImmediatePropagation();	
	});	
		
	$("#btn-cancel").click(function(e) {
		e.preventDefault();	
	});			
	
}
init();

$( document ).ajaxComplete(function() {
  init();
});


JS;
$this->registerJs($js);
?>