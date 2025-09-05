<?php

use common\models\GlobalFunctions;
use kartik\builder\Form;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $model \backend\models\business\SupplierBankInformation */
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
		                      'action' => $model->isNewRecord ? Url::to(['/supplier-bank-information/create_ajax'], GlobalFunctions::URLTYPE): Url::to(["/supplier-bank-information/update_ajax?id=".$model->id.""], GlobalFunctions::URLTYPE)
		        ]);?>
            	<?= Html::hiddenInput('supplier_id', $model->supplier_id); ?>
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'banck_name')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'checking_account')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>                
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'customer_account')->textInput(['maxlength' => true]) ?>
                        </div>

                        <div class="col-md-6">
                            <?= $form->field($model, 'mobile_account')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>
					<?php
                    $label_btn= $model->isNewRecord ? Yii::t('backend', 'Crear') : Yii::t('backend', 'Actualizar');
                    ?>
                    <div class="form-group pull-right">
                      <?= Html::a('<i class="glyphicon glyphicon-remove text-danger"></i> '.Yii::t('backend','Cancelar'), ['index'], ['data-pjax'=>0, 'class' => 'btn btn-default', 'id'=>'btn-cancel-bank']); ?>&nbsp;
                      <?= Html::submitButton("<i class=\"glyphicon glyphicon-floppy-save text-success\"></i> ".$label_btn, ['class'=> 'btn btn-default', 'name'=>'btnguardar']); ?>
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
				$.pjax.reload({container: '#grid-bank-pjax', timeout: 2000});
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
		
$("#btn-cancel-bank").click(function(e) {
	e.preventDefault();
	showPanels();
});			

function showPanels()
{
	$("#panel-form-bank").html('');
	$("#panel-grid-bank").show(15);
	$.pjax.reload({container: '#grid-bank-pjax', timeout: 2000});				
}
JS;
$this->registerJs($js);
?>
