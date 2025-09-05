<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\number\NumberControl;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\UtilsConstants;

/* @var $this yii\web\View */
/* @var $model \backend\models\business\ItemInvoice */
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
		                      'action' => $model->isNewRecord ? ['/item-manual-invoice/create_ajax']: ["/item-manual-invoice/update_ajax?id=".$model->id.""]
		        ]);?>
            	<?= Html::hiddenInput('invoice_id', $model->invoice_id); ?>
                <div class="row">
                    <div class="col-md-7">
                        <?= '<b>'.Yii::t('backend','Descripción').': </b><br>'.$model->description ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <?=
                        $form->field($model, "quantity")->widget(NumberControl::classname(), [
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
                    <div class="col-md-2">
                        <?=
                        $form->field($model, "price")->widget(NumberControl::classname(), [
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
                    <div class="col-md-2">
                        <?=
                        $form->field($model, "unit_type_id")->widget(Select2::classname(), [
                            "data" => UnitType::getSelectMapProfessionalService(true,true),
                            "language" => Yii::$app->language,
                            "options" => ["placeholder" => "----", "multiple"=>false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                        ]);
                        ?>
                    </div>
                </div>
                <input type="hidden" id="discount_percent" value="0" />
                <?php
                $labelbtn = $model->isNewRecord ? Yii::t('backend', 'Crear') : Yii::t('backend', 'Actualizar');
                ?>
                <div class="form-group pull-right">
                    <?= Html::a('<i class="glyphicon glyphicon-remove text-danger"></i> '.Yii::t('backend','Cancelar'), ['index'], ['data-pjax'=>0, 'class' => 'btn btn-default', 'id'=>'btn-cancel-item_invoice']); ?>&nbsp;
                    <?= Html::submitButton("<i class=\"glyphicon glyphicon-floppy-save text-success\"></i> ".$labelbtn, ['class'=> 'btn btn-default', 'name'=>'btnguardar']); ?>
                </div>
                <?php ActiveForm::end(); ?>
			</div>
		</div>
	</div>
</div>

<?php
$url_get_price_by_type = '';
$box_quantity = 1;
$package_quantity = 1;

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
                    $.pjax.reload({container: '#grid-item_manual_invoice-pjax', timeout: 2000});
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
		
    $("#btn-cancel-item_invoice").click(function(e) {
        e.preventDefault();
        showPanels();
    });			
    
    function showPanels() {
        $("#panel-form-item_invoice").html('');
        $("#panel-grid-item_invoice").show(15);	
        $.pjax.reload({container: '#grid-item_manual_invoice-pjax', timeout: 2000});			
    }
    });
JS;
$this->registerJs($js);
?>
