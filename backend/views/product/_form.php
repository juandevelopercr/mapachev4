<?php
use kartik\widgets\FileInput;
use common\models\GlobalFunctions;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use backend\models\nomenclators\Cabys;
use backend\models\nomenclators\Family;
use backend\models\nomenclators\Category;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\BranchOffice;
use backend\models\business\Supplier;
use backend\models\nomenclators\InventoryType;
use yii\web\JsExpression;
use yii\helpers\Url;
use kartik\depdrop\DepDrop;
use backend\models\nomenclators\TaxType;
use kartik\number\NumberControl;
use backend\models\business\ProductHasBranchOffice;
use wbraganca\dynamicform\DynamicFormWidget;
use backend\models\business\Sector;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Product */
/* @var $form yii\widgets\ActiveForm */

?>

    <div class="row">
        <div class="col-md-3">
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'code')->textInput(['maxlength' => true,'readonly'=>true]) ?>
                </div>
                <div class="col-md-12">
                    <?=
                    $form->field($model, "image")->widget(FileInput::classname(), [
                        "language" => Yii::$app->language,
                        "pluginOptions" => GlobalFunctions::getConfigFileInputWithPreview($model->getImageFile(), $model->id),
                    ]);
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="row">
                <div class="col-md-8">
                    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>
                </div>

                <div class="col-md-4">
                    <?=
                    $form->field($model, "entry_date")->widget(DateControl::classname(), [
                        "type" => DateControl::FORMAT_DATE
                    ])
                    ?>
                </div>

            </div>
            <div class="row">
                <div class="col-md-12">
                    <?php
                    // The controller action that will render the list
                    $url_cabys = Url::to(['cabys/cabys_list'], GlobalFunctions::URLTYPE);

                    // Get the initial values
                    $init_value_cabys = empty($model->cabys_id) ? '' : Cabys::getLabelSelectById($model->cabys_id);

                    echo $form->field($model, 'cabys_id')->widget(Select2::classname(), [
                        'initValueText' => $init_value_cabys, // set the initial display text
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple"=>false],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 2,
                            'language' => [
                                'errorLoading' => new JsExpression("function () { return 'Buscando resultados...'; }"),
                            ],
                            'ajax' => [
                                'url' => $url_cabys,
                                'dataType' => 'json',
                                'data' => new JsExpression('function(params) { return {q:params.term}; }')
                            ],
                            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                            'templateResult' => new JsExpression('function(cabys) { return cabys.text; }'),
                            'templateSelection' => new JsExpression('function (cabys) { return cabys.text; }'),
                        ],
                    ]);
                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'bar_code')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'supplier_code')->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?=
                    $form->field($model, "family_id")->widget(Select2::classname(), [
                        "data" => Family::getSelectMap(),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple"=>false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ]);
                    ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'category_id')->widget(DepDrop::classname(), [
                        'type'=>DepDrop::TYPE_SELECT2,
                        'data'=>($model->family_id > 0) ? Category::getSelectMapSpecific($model->family_id): array(),
                        'options'=>['placeholder'=> "----"],
                        'select2Options'=>['pluginOptions'=>['allowClear'=>true]],
                        'pluginOptions'=>[
                            'depends'=>['product-family_id'],
                            'url'=>Url::to(['/util/get_categories'], GlobalFunctions::URLTYPE),
                            'params'=>['input-type-1', 'input-type-2']
                        ]
                    ]);
                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?=
                    $form->field($model, "inventory_type_id")->widget(Select2::classname(), [
                        "data" => InventoryType::getSelectMap(),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple"=>false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ]);
                    ?>
                </div>
                <div class="col-md-6">
                    <?=
                    $form->field($model, "unit_type_id")->widget(Select2::classname(), [
                        "data" => UnitType::getSelectMapByCode('Unid'),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple"=>false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ]);
                    ?>
                </div>
            </div>

            <div class="row">

                <div class="col-md-6">
                    <?= $form->field($model, 'branch')->textInput(['maxlength' => true]) ?>
                </div>

                <div class="col-md-6">
                    <?=
                    $form->field($model, "suppliers")->widget(Select2::classname(), [
                        "data" => Supplier::getSelectMap(),
                        "language" => Yii::$app->language,
                        'maintainOrder' => true,
                        "options" => [
                            "placeholder" => "----",
                            "multiple"=>true],
                        "pluginOptions" => [
                            "allowClear" => true
                        ]
                    ]);
                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <?=
                    $form->field($model, "min_quantity")->widget(NumberControl::classname(), [
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 0
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>
                </div>
                <div class="col-md-3">
                    <?=
                    $form->field($model, "max_quantity")->widget(NumberControl::classname(), [
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 0
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>
                </div>
                <div class="col-md-3">
                    <?=
                    $form->field($model, "package_quantity")->widget(NumberControl::classname(), [
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 0
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>
                </div>
                <div class="col-md-3">
                    <?=
                    $form->field($model, "quantity_by_box")->widget(NumberControl::classname(), [
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 0
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php
$url_get_cabys_value = Url::to(['cabys/get_percent'], GlobalFunctions::URLTYPE);
$id_generic_tax_type = TaxType::getIdTaxGeneric();

$js_main = <<<JS
$(document).ready(function(e) {
    
		$("#product-cabys_id").change(function() 
		{
		    var id_value = $(this).val();
		    if(id_value != '' && id_value != null)
            {
                $.ajax({
                    type: "GET",
                    url : "$url_get_cabys_value?id="+id_value,     
                    success : function(response) 
                    {
                        var percent = response.percent;
                        $("#product-tax_rate_percent-disp").val(percent);				
                        $("#product-tax_rate_percent").val(percent);
                        $("#product-tax_type_id").val("$id_generic_tax_type").trigger("change");	
                
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
		});
});
JS;

// Register action buttons js
$this->registerJs($js_main);
?>

