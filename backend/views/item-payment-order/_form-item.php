<?php
use backend\models\business\ItemPaymetOrderForm;
use common\models\GlobalFunctions;
use kartik\number\NumberControl;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;


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
		                      'action' => $model->isNewRecord ? Url::to(['/item-payment-order/create_ajax'], GlobalFunctions::URLTYPE) : Url::to(['/item-payment-order/update_ajax', 'id' => $model->id], GlobalFunctions::URLTYPE)
		        ]);?>
            	<?= Html::hiddenInput('payment_order_id', $model->payment_order_id); ?>
                <div class="row">
                    <div class="col-sm-6">
                        <?=
                        $form->field($model, "product_id")->widget(Select2::classname(), [
                            "data" => ItemPaymetOrderForm::getSelectMap(true),
                            "language" => Yii::$app->language,
                            "options" => ["placeholder" => "----", "multiple"=>false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                        ]);
                        ?>
                    </div>
                    <div class="col-sm-3">
                        <?=
                        $form->field($model, "payment_order_quantity")->widget(NumberControl::classname(), [
                            "maskedInputOptions" => [
                                "allowMinus" => false,
                                "groupSeparator" => ".",
                                "radixPoint" => ",",
                                "digits" => 5
                            ],
                            "displayOptions" => ["class" => "form-control kv-monospace"],
                            "saveInputContainer" => ["class" => "kv-saved-cont"]
                        ])
                        ?>
                    </div>

                    <div class="col-sm-1">
                        <?= Html::submitButton("<i class=\"glyphicon glyphicon-floppy-save text-success\"></i> ".Yii::t('backend', 'Agregar'), ['class'=> 'btn btn-default margin-top-10', 'name'=>'btnguardar']); ?>
                    </div>
                </div>
			    <?php ActiveForm::end(); ?>
			</div>
		</div>
	</div>
</div>

<?php

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
				$.pjax.reload({container: '#grid-item_payment_order-pjax', timeout: 2000});
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
JS;
$this->registerJs($js);
?>
