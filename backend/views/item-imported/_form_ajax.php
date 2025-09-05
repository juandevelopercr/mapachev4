<?php
use backend\models\business\ItemImported;
use backend\models\business\Product;
use backend\models\business\SectorLocation;
use common\models\GlobalFunctions;
use kartik\number\NumberControl;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model \backend\models\business\ItemImported */
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
		                      'action' => $model->isNewRecord ? Url::to(['/item-imported/create_ajax'], GlobalFunctions::URLTYPE): Url::to(["/item-imported/update_ajax?id=".$model->id.""], GlobalFunctions::URLTYPE)
		        ]);?>
            	<?= Html::hiddenInput('entry_id', $model->entry_id); ?>
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-9">
                        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <?=
                        $form->field($model, "price_by_unit")->widget(NumberControl::classname(), [
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
                    <div class="col-md-3">
                        <?=
                        $form->field($model, "amount_total")->widget(NumberControl::classname(), [
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
                </div>
                <div class="row">
                    <div class="col-md-4">

                        <?=
                        $form->field($model, "sector_location_id")->widget(Select2::classname(), [
                            "data" => SectorLocation::getSelectMap(false,null,$model->xmlImported->entry->branch_office_id),
                            "language" => Yii::$app->language,
                            "options" => ["placeholder" => "----", "multiple"=>false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                        ]);
                        ?>
                    </div>
                    <div class="col-md-3">
                        <?=
                        $form->field($model, "status")->widget(Select2::classname(), [
                            'disabled' => ($model->status == ItemImported::STATUS_READY_TO_APPROV || $model->status == ItemImported::STATUS_PRODUCT_NOT_FOUND || $model->status == ItemImported::STATUS_NOT_LOCATION),
                            "data" => ItemImported::getStatusSelectType(),
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
                    $labelbtn = $model->isNewRecord ? Yii::t('backend', 'Crear') : Yii::t('backend', 'Actualizar');
                    ?>
                    <div class="form-group pull-right">
                      <?= Html::a('<i class="glyphicon glyphicon-remove text-danger"></i> '.Yii::t('backend','Cancelar'), ['index'], ['data-pjax'=>0, 'class' => 'btn btn-default', 'id'=>'btn-cancel-item_imported']); ?>&nbsp;
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
    refresh_prices();
    
    $("#itemimported-quantity-disp").keyup(function (e) {
		refresh_prices();
	});	
	
    $("#itemimported-price_by_unit-disp").keyup(function (e) {
        refresh_prices();
    });	
    
    function refresh_prices() {
        price = $("#itemimported-price_by_unit-disp").val().replace(/\./g,"");
        price = price.replace(",",".");
        price = parseFloat(price);
        
        quantity = $("#itemimported-quantity-disp").val().replace(/\./g,"");
        quantity = quantity.replace(",",".");
        quantity = parseFloat(quantity);
        
        new_amount = (price * quantity);
        $("#itemimported-amount_total-disp").val(new_amount);
        $("#itemimported-amount_total").val(new_amount);
    }
    
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
                    $.pjax.reload({container: '#grid-item_imported-pjax', timeout: 2000});
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
		
    $("#btn-cancel-item_imported").click(function(e) {
        e.preventDefault();
        showPanels();
    });			
    
    function showPanels() {
        $("#panel-form-item_imported").html('');
        $("#panel-grid-item_imported").show(15);	
        $.pjax.reload({container: '#grid-item_imported-pjax', timeout: 2000});			
    }
    });
JS;
$this->registerJs($js);
?>

