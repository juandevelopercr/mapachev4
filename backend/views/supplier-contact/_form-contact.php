<?php
use backend\models\nomenclators\Department;
use backend\models\nomenclators\JobPosition;
use common\models\GlobalFunctions;
use kartik\number\NumberControl;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model \backend\models\business\SupplierContact */
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
		                      'action' => $model->isNewRecord ? Url::to(['/supplier-contact/create_ajax'], GlobalFunctions::URLTYPE): Url::to(["/supplier-contact/update_ajax?id=".$model->id.""], GlobalFunctions::URLTYPE)
		        ]);?>
            	<?= Html::hiddenInput('supplier_id', $model->supplier_id); ?>
                <div class="row">
                    <div class="col-sm-4">  
                        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                    </div>                    
                    <div class="col-sm-4">  
                        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-sm-3">
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
                    <div class="col-sm-1">                              
                        <?= $form->field($model, 'ext')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>                
                <div class="row">
                    <div class="col-sm-4">
                        <?=
                        $form->field($model, "cellphone")->widget(NumberControl::classname(), [
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
                    <div class="col-sm-4">
                        <?=
                        $form->field($model, "department_id")->widget(Select2::classname(), [
                            "data" => Department::getSelectMap(),
                            "language" => Yii::$app->language,
                            "options" => ["placeholder" => "----", "multiple"=>false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                        ]);
                        ?>
                    </div>
                    <div class="col-sm-4">
                        <?=
                        $form->field($model, "job_position_id")->widget(Select2::classname(), [
                            "data" => JobPosition::getSelectMap(),
                            "language" => Yii::$app->language,
                            "options" => ["placeholder" => "----", "multiple"=>false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                        ]);
                        ?>
                    </div>
                </div>
					<?php
                    $accion_aplicar_salir = $model->isNewRecord ? Yii::t('backend', 'Crear') : Yii::t('backend', 'Actualizar');
                    ?>
                    <div class="form-group pull-right">
                      <?= Html::a('<i class="glyphicon glyphicon-remove text-danger"></i> '.Yii::t('backend','Cancelar'), ['index'], ['data-pjax'=>0, 'class' => 'btn btn-default', 'id'=>'btn-cancel-contact']); ?>&nbsp;
                      <?= Html::submitButton("<i class=\"glyphicon glyphicon-floppy-save text-success\"></i> ".$accion_aplicar_salir." ", ['class'=> 'btn btn-default', 'name'=>'btnguardar']); ?>  
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
				$.pjax.reload({container: '#grid-contact-pjax', timeout: 2000});
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
				"message": "Ha ocurrido un error. Inténtelo nuevamente, si el error persiste, póngase en contact con el administrador del sistema",
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
		
$("#btn-cancel-contact").click(function(e) {
	e.preventDefault();
	showPanels();
});			

function showPanels()
{
	$("#panel-form-contact").html('');
	$("#panel-grid-contact").show(15);	
	$.pjax.reload({container: '#grid-contact-pjax', timeout: 2000});			
}
JS;
$this->registerJs($js);
?>
