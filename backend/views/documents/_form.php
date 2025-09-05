<?php

use backend\models\nomenclators\UtilsConstants;
use common\models\GlobalFunctions;
use kartik\file\FileInput;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Documents */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box-body">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <!-- /.box-header -->
                <div class="box-body">
                    <?php
                    $form = ActiveForm::begin([
                        'options' => ['enctype' => 'multipart/form-data'], 'id' => $model->formName(),
                        'action' => $model->isNewRecord ? Url::to(['/documents/create'], GlobalFunctions::URLTYPE) : Url::to(["/documents/update?id=" . $model->id . ""], GlobalFunctions::URLTYPE)
                    ]); ?>
                    <input type="hidden" id="documento_id" value="<?= $model->id ?>" />
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-warning alert-dismissible">
                                <h4><i class="icon fa fa-info"></i>Información!</h4>
                                <span id="textmsg">
                                    1- Seleccione el comprobante xml, el sistema verificará que los datos del receptor
                                    se correspondan con los datos de la empresa. <br />
                                    2- Seleccione un mensaje de aceptación o rechazo del documento y presione el botón "Guardar Mensaje", el sistema realizará la validación del documento y
                                    lo guardará en el sistema sin enviarlo a Hacienda en este momento.
                                    <span style="color:red; font-weight:bold">!Nuevo!</span><br />
                                    3- El sistema ejecutará un proceso automático que verificará que el documento haya sido aceptado por "Hacienda".
                                    Con esto se mejora la gestión de documentos y la experiencia de usuario. Si el comprobante presenta algún problema se le notificará via email, además de mostrar el estado del
                                    Documento en la columna denominada "Mensaje de Aceptación".
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'url_xml')->widget(FileInput::classname(), [
                                'options' => [
                                    'accept' => 'text/xml',
                                ],
                                'pluginOptions' => [
                                    'previewFileType' => 'image',
                                    'showUpload'      => false,
                                    'browseLabel'     => Yii::t('app', 'Browse &hellip;'),
                                ],
                                'disabled' => !$model->isNewRecord,
                            ]);
                            $fichero = $model->getFileUrlXML();
                            $filesize_xml = file_exists($fichero) ? filesize($fichero) : 0;

                            $fichero_pdf = $model->getFileUrlPDF();
                            $filesize_pdf = file_exists($fichero_pdf) ? filesize($fichero_pdf) : 0;

                            ?>

                            <?php if (isset($model->url_xml) && !empty($model->url_xml)) : ?>


                                <div class="thumbnail">
                                    <?php
                                    $content = '';
                                    $template = '';
                                    $pos = strpos($model->url_xml, ".");
                                    if ($pos === false) { // nota: tres signos igual
                                        // no encontrado ...
                                    } else {
                                        $content = "<object class=\"kv-preview-data file-object type-object\" data=\"" . $model->getFileUrlXML() . "\" type=\"type-object\" 'width=\"160\" height=\"160\"><param name=\"movie\" value=\"" . $model->key . "\" /> 
                                                <param name=\"controller\" value=\"true\" />
                                                <param name=\"allowFullScreen\" value=\"true\" />
                                                <param name=\"allowScriptAccess\" value=\"always\" />
                                                <param name=\"autoPlay\" value=\"false\" />
                                                <param name=\"autoStart\" value=\"false\" />
                                                <param name=\"quality\" value=\"high\" />
                                                <div class=\"file-preview-other\">
                                                    <span class=\"file-other-icon\"><i class=\"glyphicon glyphicon-file\"></i></span>
                                                </div>
                                                </object>";
                                        $template = "object";
                                    }
                                    ?>
                                    <div class="file-preview">
                                        <div class="close fileinput-remove">×</div>
                                        <div class="file-drop-disabled">
                                            <div class="file-preview-thumbnails">
                                                <div class="file-preview-frame krajee-default  kv-preview-thumb" id="preview-1518574308225-0" data-fileindex="0" data-template="<?= $template; ?>" title="<?= $model->key; ?>">
                                                    <div class="kv-file-content">
                                                        <?= $content ?>
                                                    </div>
                                                    <div class="file-thumbnail-footer">
                                                        <div class="file-footer-caption" title="<?= $model->key; ?>"><?= $model->key; ?><br>
                                                            <samp>(<?= $filesize_xml ?> Bytes)</samp>
                                                        </div>
                                                        <div class="file-upload-indicator" title="No subido todavía"><i class="glyphicon glyphicon-hand-down text-warning"></i></div>
                                                        <div class="file-actions">
                                                            <div class="file-footer-buttons">
                                                                <a href="<?= $model->getFileUrlXML(); ?>" class="kv-file-zoom btn btn-xs btn-default" title="Ver detalles" target="_blank"><i class="glyphicon glyphicon-zoom-in"></i></a>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="kv-zoom-cache" style="display:none">
                                                    <div class="file-preview-frame krajee-default  kv-zoom-thumb" id="zoom-preview-1518574308225-0" data-fileindex="0" data-template="<?= $template; ?>" title="<?= $model->key; ?>">
                                                        <div class="kv-file-content">
                                                            <?= $content ?>
                                                        </div>
                                                        <div class="file-thumbnail-footer">
                                                            <div class="file-footer-caption" title="<?= $model->key; ?>"><?= $model->key; ?><br>
                                                                <samp>(<?= $filesize_xml ?> Bytes)</samp>
                                                            </div>
                                                            <div class="file-upload-indicator" title="No subido todavía"><i class="glyphicon glyphicon-hand-down text-warning"></i></div>
                                                            <div class="file-actions">
                                                                <div class="file-footer-buttons">
                                                                    <a href="<?= $model->getFileUrlXML(); ?>" class="kv-file-zoom btn btn-xs btn-default" title="Ver detalles" target="_blank"><i class="glyphicon glyphicon-zoom-in"></i></a>
                                                                </div>
                                                                <div class="clearfix"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                            <div class="file-preview-status text-center text-success"></div>
                                            <div style="display: none;" class="kv-fileinput-error file-error-message"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif ?>
                        </div>




                        <div class="col-md-6">
                            <?= $form->field($model, 'url_pdf')->widget(FileInput::classname(), [
                                'options' => [
                                    'accept' => '.pdf',
                                ],
                                'pluginOptions' => [
                                    'previewFileType' => 'image',
                                    'showUpload'      => false,
                                    'browseLabel'     => Yii::t('app', 'Browse &hellip;'),
                                ],
                            ]);
                            $fichero = $model->getFileUrlPDF();
                            $filesize_xml = file_exists($fichero) ? filesize($fichero) : 0;
                            ?>

                            <?php if (isset($model->url_pdf) && !empty($model->url_pdf)) : ?>


                                <div class="thumbnail">
                                    <?php
                                    $content = '';
                                    $template = '';
                                    $pos = strpos($model->url_pdf, ".");
                                    if ($pos === false) { // nota: tres signos igual
                                        // no encontrado ...
                                    } else {
                                        $content = "<object class=\"kv-preview-data file-object type-object\" data=\"" . $model->getFileUrlPDF() . "\" type=\"type-object\" 'width=\"160\" height=\"160\"><param name=\"movie\" value=\"" . $model->key . "\" /> 
                                                <param name=\"controller\" value=\"true\" />
                                                <param name=\"allowFullScreen\" value=\"true\" />
                                                <param name=\"allowScriptAccess\" value=\"always\" />
                                                <param name=\"autoPlay\" value=\"false\" />
                                                <param name=\"autoStart\" value=\"false\" />
                                                <param name=\"quality\" value=\"high\" />
                                                <div class=\"file-preview-other\">
                                                    <span class=\"file-other-icon\"><i class=\"glyphicon glyphicon-file\"></i></span>
                                                </div>
                                                </object>";
                                        $template = "object";
                                    }
                                    ?>
                                    <div class="file-preview">
                                        <div class="close fileinput-remove">×</div>
                                        <div class="file-drop-disabled">
                                            <div class="file-preview-thumbnails">
                                                <div class="file-preview-frame krajee-default  kv-preview-thumb" id="preview-1518574308225-0" data-fileindex="0" data-template="<?= $template; ?>" title="<?= $model->key; ?>">
                                                    <div class="kv-file-content">
                                                        <?= $content ?>
                                                    </div>
                                                    <div class="file-thumbnail-footer">
                                                        <div class="file-footer-caption" title="<?= $model->key; ?>"><?= $model->key; ?><br>
                                                            <samp>(<?= $filesize_pdf ?> Bytes)</samp>
                                                        </div>
                                                        <div class="file-upload-indicator" title="No subido todavía"><i class="glyphicon glyphicon-hand-down text-warning"></i></div>
                                                        <div class="file-actions">
                                                            <div class="file-footer-buttons">
                                                                <a href="<?= $model->getFileUrlPDF(); ?>" class="kv-file-zoom btn btn-xs btn-default" title="Ver detalles" target="_blank"><i class="glyphicon glyphicon-zoom-in"></i></a>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="kv-zoom-cache" style="display:none">
                                                    <div class="file-preview-frame krajee-default  kv-zoom-thumb" id="zoom-preview-1518574308225-0" data-fileindex="0" data-template="<?= $template; ?>" title="<?= $model->key; ?>">
                                                        <div class="kv-file-content">
                                                            <?= $content ?>
                                                        </div>
                                                        <div class="file-thumbnail-footer">
                                                            <div class="file-footer-caption" title="<?= $model->key; ?>"><?= $model->key; ?><br>
                                                                <samp>(<?= $filesize_pdf ?> Bytes)</samp>
                                                            </div>
                                                            <div class="file-upload-indicator" title="No subido todavía"><i class="glyphicon glyphicon-hand-down text-warning"></i></div>
                                                            <div class="file-actions">
                                                                <div class="file-footer-buttons">
                                                                    <a href="<?= $model->getFileUrlPDF(); ?>" class="kv-file-zoom btn btn-xs btn-default" title="Ver detalles" target="_blank"><i class="glyphicon glyphicon-zoom-in"></i></a>
                                                                </div>
                                                                <div class="clearfix"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                            <div class="file-preview-status text-center text-success"></div>
                                            <div style="display: none;" class="kv-fileinput-error file-error-message"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <?=
                            $form->field($model, "document_type")->widget(Select2::classname(), [
                                "data" => UtilsConstants::getDocumentType(),
                                "language" => "es",
                                "options" => ["placeholder" => Yii::t("app", "-- Selecione --"), "multiple" => false],
                                "pluginOptions" => [
                                    "allowClear" => true
                                ],
                                'disabled' => !$model->isNewRecord && $model->status != 1,
                            ]);
                            ?>
                        </div>                        
                        <div class="col-md-6">
                            <?= $form->field($model, 'message_detail')->textInput(['maxlength' => true]); ?>
                        </div>
                        <div class="col-md-3">
                            <?=
                            $form->field($model, "status")->widget(Select2::classname(), [
                                "data" => $model->status > 2 ? UtilsConstants::getDocumentStatusSelectType() : UtilsConstants::getDocumentStatusOnlyAceptadoSelectType(),
                                "language" => "es",
                                "options" => ["placeholder" => Yii::t("app", "-- Selecione --"), "multiple" => false],
                                "pluginOptions" => [
                                    "allowClear" => true
                                ],
                                'disabled' => !$model->isNewRecord && $model->status != 1,
                            ]);
                            ?>
                        </div>
                    </div>
                    <?php
                    $accion_aplicar = 'Guardar Documento';
                    //$accion_aplicar_salir = $model->isNewRecord ? 'Crear & Cerrar' : 'Guardar & Cerrar';
                    ?>
                    <div class="form-group pull-right">
                        <?= Html::a('<i class="glyphicon glyphicon-remove text-danger"></i> Cancelar', ['index'], ['data-pjax' => 0, 'class' => 'btn btn-default']); ?>
                        &nbsp;
                        <?= Html::submitButton("<i class='glyphicon glyphicon-ok text-primary'></i><span id='textbtn'> " . $accion_aplicar . "</span>", ['class' => 'btn btn-default', 'name' => 'btnaplicar']); ?>
                        &nbsp;
                        <?php //Html::submitButton("<i class='glyphicon glyphicon-floppy-save text-success'></i> ".$accion_aplicar_salir."", ['class'=> 'btn btn-default', 'name'=>'btnguardar']); 
                        ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>


<?php
$url = Url::to(['index'], GlobalFunctions::URLTYPE);
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
		var id = $("#documento_id").val();
		if (parseInt(id) > 0)
			var url = 'update?id='+id;
		else
			var url = 'create';
			
//		alert(url);			
//		
		$.LoadingOverlay("show");		
		var formAction = $(this).attr('action');
		$.ajax({
			type        : 'POST',
			url         : url,
			cache       : false,
			data        : formdata ? formdata : form.serialize(),
			contentType : false,
			processData : false,
	
			success: function(response) {
					$.LoadingOverlay("hide");							
					$.notify({
							"message": response.mensaje,
							"icon": "glyphicon glyphicon-ok-sign",
							"title": response.titulo,						
							"showProgressbar": false,
							"url":"",						
							"target":"_blank"},{"type": response.type});
					//var url = "/facturacion/documentos-recibidos/index";
					if (response.regresar == 1)
						window.location.href= '/documents/index';															
					//$("#documento_id").val(response.documento_id);
					//$("#textbtn").html(' Guardar');
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$.LoadingOverlay("hide");
				$.notify({
					"message": "Ha ocurrido un error. Inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema: "+errorThrown,
					"icon": "glyphicon glyphicon-remove text-danger-sign",
					"title": "Error <hr class=\"kv-alert-separator\">",					
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
		e.stopImmediatePropagation();	
	});
	
	$("#btn-cancel-producto").click(function(e) {
		e.preventDefault();
		//mostrarPaneles();
	});			
	
}
init();
JS;
    $this->registerJs($js);
    ?>