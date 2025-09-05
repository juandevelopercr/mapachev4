<?php
use backend\models\business\Sector;
use backend\models\business\SectorLocation;
use common\models\GlobalFunctions;
use kartik\number\NumberControl;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model \backend\models\business\PhysicalLocation */
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
		                      'action' => Url::to(['/physical-location/create_ajax'], GlobalFunctions::URLTYPE)
		        ]);?>
            	<?= Html::hiddenInput('product_id', $model->product_id); ?>

                <div class="row">
                    <div class="col-sm-6">
                        <?=
                        $form->field($model, "sector_location_id")->widget(Select2::classname(), [
                            "data" => SectorLocation::getSelectMap(false,$model->product_id),
                            "language" => Yii::$app->language,
                            "options" => ["placeholder" => 'Ubicación [Sector] (Sucursal)', "multiple"=>false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                        ]);
                        ?>
                    </div>

                    <div class="col-sm-3">
                        <?=
                        $form->field($model, "quantity")->widget(NumberControl::classname(), [
                            "maskedInputOptions" => [
                                "allowMinus" => false,
                                "groupSeparator" => ".",
                                "radixPoint" => ",",
                                "digits" => 5
                            ],
                            "displayOptions" => ["class" => "form-control kv-monospace"],
                            "saveInputContainer" => ["class" => "kv-saved-cont"]
                        ])->label(Yii::t('backend','Cantidad inicial'))
                        ?>
                    </div>

                </div>
					<?php
                    $labelbtn = $model->isNewRecord ? Yii::t('backend', 'Crear') : Yii::t('backend', 'Actualizar');
                    ?>
                    <div class="form-group pull-right">
                      <?= Html::a('<i class="glyphicon glyphicon-remove text-danger"></i> '.Yii::t('backend','Cancelar'), ['index'], ['data-pjax'=>0, 'class' => 'btn btn-default', 'id'=>'btn-cancel-physical_location']); ?>&nbsp;
                      <?= Html::submitButton("<i class=\"glyphicon glyphicon-floppy-save text-success\"></i> ".$labelbtn, ['class'=> 'btn btn-default', 'name'=>'btnguardar']); ?>
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
		    $.notify({
                "message": response.message,
                "icon": "glyphicon glyphicon-ok-sign",
                "title": response.titulo,						
                "showProgressbar": false,
                "url":"",						
                "target":"_blank"},{"type": response.type}
            );
		    
		    if(response.type == 'danger')
            {                
                $.each(response.errors, function(key, val) {
                    $("#physicallocation-"+key).after("<div class=\"help-block\">"+val+"</div>");
                    $("#physicallocation-"+key).closest(".form-group").addClass("has-error");
                    
                });
            }
            else
            {
               $.pjax.reload({container: '#grid-physical_location-pjax', timeout: 2000});
				
               showPanels(); 
            }
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
		
$("#btn-cancel-physical_location").click(function(e) {
	e.preventDefault();
	showPanels();
});			

function showPanels()
{
	$("#panel-form-physical_location").html('');
	$("#panel-grid-physical_location").show(15);	
	$.pjax.reload({container: '#grid-physical_location-pjax', timeout: 2000});			
}
JS;
$this->registerJs($js);
?>
