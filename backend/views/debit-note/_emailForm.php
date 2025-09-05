<?php
use yii\helpers\Html;
use kartik\form\ActiveForm;
use dosamigos\ckeditor\CKEditor;
use common\models\GlobalFunctions;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model \backend\models\business\DebitNote */
/* @var $form yii\widgets\ActiveForm */
$this->title = 'Enviar Nota de Débito';
?>

<div class="row">
    <div class="col-xs-12">
	<div class="box box-primary">
	<!-- /.box-header -->
            <div class="box-body">
		<?php  		
				$form = ActiveForm::begin([
				'options' => ['enctype' => 'multipart/form-data'], 'id' => 'formemail',
							  'action' => Url::to(["/debit-note/enviar-factura-email?id=". $model->id], GlobalFunctions::URLTYPE)
				]);
				//echo $form->field($model, 'archivo')->hiddenInput()->label(false);
				echo $form->field($model, 'id')->hiddenInput()->label(false);
		?>
				<h3>Enviar Notas de Débito Mediante Correo Electrónico</h3>
                <div class="row">
					<div class="col-md-4">
						<?= $form->field($model, 'de')->textInput(['maxlength' => true]);?>
					</div>
					<div class="col-md-4">
						<?= $form->field($model, 'para')->textInput(['maxlength' => true, 'redonly'=>true]) ?>
					</div>
					<div class="col-md-4">
						<?= $form->field($model, 'cc')->textInput(['maxlength' => true]) ?>
					</div>
				</div>
                <div class="row">
					<div class="col-md-6">
						<?= $form->field($model, 'nombrearchivo')->textInput(['maxlength' => true, 'redonly'=>true]) ?>
					</div>
					<div class="col-md-6">
						<?= $form->field($model, 'asunto')->textInput(['maxlength' => true]) ?>
					</div>
				</div>
                <div class="row">
					<div class="col-md-12">
                        <?=
                        $form->field($model, "cuerpo")->widget(CKEditor::className(), [
                            "preset" => "custom",
                            "clientOptions" => [
                                "toolbar" => GlobalFunctions::getToolBarForCkEditor(),
                            ],
                        ])
                        ?>
					</div>
                </div>
				<div class="form-group pull-right" style="display:none;" id="divespera">
		        	<img src="/loading.gif">
		        </div>

                <div class="form-group pull-right">
                  <?= Html::a('<i class="glyphicon glyphicon-remove text-danger"></i> Cancelar', ['#'], ['data-pjax'=>0, 'class' => 'btn btn-default', 'id'=>"btn-cancelar"]); ?>&nbsp;
                  <?= Html::submitButton("<i class=\"glyphicon glyphicon-envelope\"></i> Enviar Factura", ['class'=> 'btn btn-default', 'name'=>'btnenviar', 'id'=>"btn-enviar"]); ?>&nbsp; 
                </div> 

                <?php ActiveForm::end(); ?>
            </div>
	</div>
    </div>
</div>


<?php
$js = <<<JS

$("#btn-cancelar").click(function(e){
	e.preventDefault();	
	$("#panel-form-enviar").hide(500);	
	$("#panel-grid").show(500);		
	$.pjax.reload({container:"#grid_debitnote-pjax"});		
})

// get the form id and set the event
$('form#formemail').on('beforeSubmit', function(e) {
	var formdata = false;
	if(window.FormData){
		formdata = new FormData($(this)[0]);
	}
	$("#btn-enviar").attr('disabled', true);
	$("#divespera").show(500);
	var formAction = $(this).attr('action');
	$.ajax({
		type        : 'POST',
		url         : formAction,
		cache       : false,
		data        : formdata ? formdata : form.serialize(),
		contentType : false,
		processData : false,

		success: function(response) {
			e.preventDefault();
			$.pjax.reload({container:"#grid_debitnote-pjax"});
			$("#panel-form-enviar").html('');
			$("#panel-form-enviar").hide(500);			
			$("#panel-grid").show(500);
			$("#btn-enviar").attr('disabled', false);
			$("#divespera").hide(500);		
			$.notify({
				"message": response.message,
				"icon": "glyphicon glyphicon-ok-sign",
				"title": response.titulo,				
				"showProgressbar": false,
				"url":"",						
				"target":"_blank"},{"type": response.type}
			);				
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			$("#btn-enviar").attr('disabled', false);
			$("#divespera").hide(500);				
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

