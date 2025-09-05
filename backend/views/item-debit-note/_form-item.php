<?php
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\UtilsConstants;
use common\models\GlobalFunctions;
use kartik\number\NumberControl;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model \backend\models\business\ItemDebitNote */
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
                    'action' => $model->isNewRecord ? Url::to(['/item-debit-note/create_ajax'], GlobalFunctions::URLTYPE): Url::to(["/item-debit-note/update_ajax?id=".$model->id.""], GlobalFunctions::URLTYPE)
                ]);?>
            	<?= Html::hiddenInput('debit_note_id', $model->debit_note_id); ?>
                <div class="row">
                    <div class="col-md-2">
                        <?= '<b>'.Yii::t('backend','Código').': </b><br>'.$model->code ?>
                    </div>
                    <div class="col-md-7">
                        <?= '<b>'.Yii::t('backend','Descripción').': </b><br>'.$model->description ?>
                    </div>
                    <div class="col-md-3">
                        <?=
                        $form->field($model, "subtotal")->widget(NumberControl::classname(), [
                            "disabled" => true,
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
                        $form->field($model, "price_unit")->widget(NumberControl::classname(), [
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
                        $form->field($model, "unit_type_id")->widget(Select2::classname(), [
                            "data" => UnitType::getSelectMap(true,true),
                            "language" => Yii::$app->language,
                            "options" => ["placeholder" => "----", "multiple"=>false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                            "disabled" => true
                        ]);
                        ?>
                    </div>

                    <div class="col-md-3">
                        <?=
                        $form->field($model, "price_type")->widget(Select2::classname(), [
                            'disabled' => (isset($model->service_id) && !empty($model->service_id)),
                            "data" => UtilsConstants::getCustomerAsssignPriceSelectType(),
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
                $labelbtn = Yii::t('backend', 'Actualizar');
                ?>
                <div class="form-group pull-right">
                    <?= Html::a('<i class="glyphicon glyphicon-remove text-danger"></i> '.Yii::t('backend','Cancelar'), ['index'], ['data-pjax'=>0, 'class' => 'btn btn-default', 'id'=>'btn-cancel-item_debit_note']); ?>&nbsp;
                    <?= Html::submitButton("<i class=\"glyphicon glyphicon-floppy-save text-success\"></i> ".$labelbtn, ['class'=> 'btn btn-default', 'name'=>'btnguardar']); ?>
                </div>
                <?php ActiveForm::end(); ?>
			</div>
		</div>
	</div>
</div>

<?php
if(isset($model->product_id))
{
    $url_get_price_by_type = \yii\helpers\Url::to(['/product/get-price-by-type','id' => $model->product_id], GlobalFunctions::URLTYPE);
    $box_quantity = (isset($model->product->quantity_by_box))? $model->product->quantity_by_box : 1;
    $package_quantity = (isset($model->product->package_quantity))? $model->product->package_quantity : 1;
}
else
{
    $url_get_price_by_type = '';
    $box_quantity = 1;
    $package_quantity = 1;
}

$js = <<<JS
$(document).ready(function(e) {
    refresh_prices();
    
    $("#itemdebitnote-quantity-disp").keyup(function (e) {
		refresh_prices();
	});	
	
    $("#itemdebitnote-price_unit-disp").keyup(function (e) {
        refresh_prices();
    });	
    
    $("#itemdebitnote-price_type").change(function() 
    {
        var id_value = $(this).val();
        if(id_value != '' && id_value != null)
        {
            var url_price_type = "$url_get_price_by_type";
            if(url_price_type != '' && url_price_type != null)
            {
                $.ajax({
                    type: "GET",
                    url : "$url_get_price_by_type&type_price="+id_value,     
                    success : function(response) 
                    {
                        $("#itemdebitnote-price_unit-disp").val(response.price);
                        $("#itemdebitnote-price_unit").val(response.price);
                        refresh_prices();
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) 
                    {
                        $.notify({
                            "message": "Ha ocurrido un error. Inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema",
                            "icon": "glyphicon glyphicon-remove text-danger-sign",
                            "title": "Informaci&oacute;n <hr class=\"kv-alert-separator\">",						
                            "showProgressbar": false,
                            "url":"",						
                            "target":"_blank"},{"type": "danger"}
                        );
                    }				
                });
            }
        }
    });
    
    $("#itemdebitnote-unit_type_id").change(function() 
    {
       refresh_prices();
    });
    
    function refresh_prices() {
        price = $("#itemdebitnote-price_unit-disp").val().replace(/\./g,"");
        price = price.replace(",",".");
        price = parseFloat(price);
        
        quantity = $("#itemdebitnote-quantity-disp").val().replace(/\./g,"");
        quantity = quantity.replace(",",".");
        quantity = parseFloat(quantity);
        
        var box_quantity = parseInt("$box_quantity");
        var package_quantity = parseInt("$package_quantity");
        
        var unit_code_selected = document.getElementById("select2-itemdebitnote-unit_type_id-container").title;
        if(unit_code_selected == 'CAJ' || unit_code_selected == 'CJ')
        {
            quantity = quantity * box_quantity;
        }
        else if(unit_code_selected == 'BULT' || unit_code_selected == 'PAQ')
        {
            quantity = quantity * package_quantity;
        }
           
        new_amount = (price * quantity);
        
        $("#itemdebitnote-subtotal-disp").val(new_amount);
        $("#itemdebitnote-subtotal").val(new_amount);
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
                    $.pjax.reload({container: '#grid_debitnote-pjax', timeout: 2000});
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
		
    $("#btn-cancel-item_debit_note").click(function(e) {
        e.preventDefault();
        showPanels();
    });			
    
    function showPanels() {
        $("#panel-form-item_debitnote").html('');
        $("#panel-grid-item_debitnote").show(15);	
        $.pjax.reload({container: '#grid_debitnote-pjax', timeout: 2000});			
    }
    });
JS;
$this->registerJs($js);
?>
