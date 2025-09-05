<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\GlobalFunctions;
use dosamigos\ckeditor\CKEditor;
use kartik\widgets\FileInput;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model \backend\models\business\AttachPo */
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
		                      'action' => $model->isNewRecord ? Url::to(['/attach-po/create_ajax'], GlobalFunctions::URLTYPE): Url::to(["/attach-po/update_ajax?id=".$model->id.""], GlobalFunctions::URLTYPE)
		        ]);?>
                <?= Html::hiddenInput('payment_order_id', $model->payment_order_id); ?>
                <div class="row">
                    <div class="col-md-4">
                        <?=
                        $form->field($model, "document_file")->widget(FileInput::classname(), [
                            "language" => Yii::$app->language,
                            "pluginOptions" => GlobalFunctions::getConfigFileInputWithPreview($model->getImageFile(), $model->id),
                        ]);
                        ?>
                    </div>
                    <div class="col-md-8">
                        <?=
                        $form->field($model, "observations")->widget(CKEditor::className(), [
                            "preset" => "custom",
                            "clientOptions" => [
                                "toolbar" => GlobalFunctions::getToolBarForCkEditor(),
                            ],
                        ])
                        ?>
                    </div>
                </div>
					<?php
                    $labelbtn = $model->isNewRecord ? Yii::t('backend', 'Crear') : Yii::t('backend', 'Actualizar');
                    ?>
                    <div class="form-group pull-right">
                      <?= Html::a('<i class="glyphicon glyphicon-remove text-danger"></i> '.Yii::t('backend','Cancelar'), ['index'], ['data-pjax'=>0, 'class' => 'btn btn-default', 'id'=>'btn-cancel-attach_po']); ?>&nbsp;
                      <?= Html::submitButton("<i class=\"glyphicon glyphicon-floppy-save text-success\"></i> ".$labelbtn, ['class'=> 'btn btn-default', 'name'=>'btnguardar']); ?>
                    </div>  
			    <?php ActiveForm::end(); ?>
			</div>
		</div>
	</div>
</div>

<?php

$js = <<<JS
$(document).ready(function(e) {
       
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
                    $.pjax.reload({container: '#grid-attach_po-pjax', timeout: 2000});
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
		
    $("#btn-cancel-attach_po").click(function(e) {
        e.preventDefault();
        showPanels();
    });			
    
    function showPanels() {
        $("#panel-form-attach_po").html('');
        $("#panel-grid-attach_po").show(15);	
        $.pjax.reload({container: '#grid-attach_po-pjax', timeout: 2000});			
    }
    });
JS;
$this->registerJs($js);
?>

