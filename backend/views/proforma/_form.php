<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\switchinput\SwitchInput;
use kartik\number\NumberControl;
use common\models\GlobalFunctions;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\Boxes;
use backend\models\business\Customer;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use common\models\User;
use backend\models\nomenclators\PaymentMethod;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Proforma */
/* @var $form yii\widgets\ActiveForm */
/* @var $searchModelItems \backend\models\business\ItemProformaSearch */
/* @var $dataProviderItems yii\data\ActiveDataProvider */

?>

<div class="box box-warning box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('backend','Proforma') ?></h3>

        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
        <!-- /.box-tools -->
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <?php
        $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

        <div class="row">
            <div class="col-md-2">
                <?= $form->field($model, 'consecutive')->textInput(['maxlength' => true, 'readonly'=>true]) ?>
            </div>

            <div class="col-md-3">
                <?=
                $form->field($model, "request_date")->widget(DateControl::classname(), [
                    "type" => DateControl::FORMAT_DATE
                ])
                ?>
            </div>

            <div class="col-md-4">
                <?=
                $form->field($model, "customer_id")->widget(Select2::classname(), [
                    "data" => Customer::getSelectMap(),
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
                $form->field($model, "invoice_type")->widget(Select2::classname(), [
                    "data" => UtilsConstants::getPreInvoiceSelectType(),
                    "language" => Yii::$app->language,
                    "options" => ["placeholder" => "----", "multiple" => false],
                    "pluginOptions" => [
                        "allowClear" => true
                    ],
                ]);
                ?>
            </div>            

            <div class="col-md-3">
                <?=
                $form->field($model, "status")->widget(Select2::classname(), [
                    'disabled' => (Yii::$app->controller->action->id == 'create'),
                    "data" => UtilsConstants::getProformaStatusSelectType(),
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
            <div class="col-md-4">
                <?=
                $form->field($model, "condition_sale_id")->widget(Select2::classname(), [
                    "data" => ConditionSale::getSelectMap(),
                    "language" => Yii::$app->language,
                    "options" => ["placeholder" => "----", "multiple"=>false],
                    "pluginOptions" => [
                        "allowClear" => true
                    ],
                ]);
                ?>
            </div>
            <div class="col-md-2">
                <?=
                $form->field($model, "credit_days_id")->widget(Select2::classname(), [
                    "data" => CreditDays::getSelectMap(),
                    "language" => Yii::$app->language,
                    "options" => ["placeholder" => "----", "multiple"=>false],
                    "pluginOptions" => [
                        "allowClear" => true
                    ],
                ]);
                ?>
            </div>
            <?php
            /*
            <div class="col-md-2">
                <?=
                $form->field($model, "discount_percent")->widget(NumberControl::classname(), [
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
            */
            ?>
            <div class="col-md-3">
                <?=
                $form->field($model, "branch_office_id")->widget(Select2::classname(), [
                    "data" => BranchOffice::getSelectMap(true, $model->branch_office_id),
                    "language" => Yii::$app->language,
                    "options" => ["placeholder" => "----", "multiple"=>false],
                    "pluginOptions" => [
                        "allowClear" => true
                    ],
                ]);
                ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'box_id')->widget(DepDrop::classname(), [
                    'type' => DepDrop::TYPE_SELECT2,
                    'data' => ($model->branch_office_id !== '') ? Boxes::getSelectMap($model->branch_office_id, $model->box_id) : array(),
                    'options' => ['placeholder' => "----"],
                    'select2Options' => ['pluginOptions' => ['allowClear' => true]],
                    'pluginOptions' => [
                        'depends' => ['proforma-branch_office_id'],
                        'url' => Url::to(['/util/get-boxes'], GlobalFunctions::URLTYPE),
                        'params' => ['input-type-1', 'input-type-2']
                    ]
                ]);
                ?>
            </div>            
        </div>

        <div class="row">
            <div class="col-md-3">
                <?=
                $form->field($model, "currency_id")->widget(Select2::classname(), [
                    "data" => Currency::getSelectMap(),
                    "language" => Yii::$app->language,
                    "options" => ["placeholder" => "----", "multiple"=>false],
                    "pluginOptions" => [
                        "allowClear" => true
                    ],
                ]);
                ?>
            </div>

            <div class="col-md-2">
                <?=
                $form->field($model, "change_type")->widget(NumberControl::classname(), [
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
                <?= $form->field($model, 'delivery_time')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-2">
                <?=
                $form->field($model, "delivery_time_type")->widget(Select2::classname(), [
                    "data" => UtilsConstants::getDeliveryTimesSelectType(),
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
                $form->field($model, "seller_id")->widget(Select2::classname(), [
                    "data" => User::getSelectMapAgents(),
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
            <div class="col-md-9">
                <?=
                $form->field($model, "payment_methods")->widget(Select2::classname(), [
                    "data" => PaymentMethod::getSelectMap(),
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
            <div class="col-md-2">
                <?=
                $form->field($model,"is_editable")->widget(SwitchInput::classname(), [
                    'disabled' => (GlobalFunctions::getRol() !== User::ROLE_SUPERADMIN),
                    "type" => SwitchInput::CHECKBOX,
                    "pluginOptions" => [
                        "onText"=> Yii::t("backend","SI"),
                        "offText"=> Yii::t("backend","NO")
                    ]
                ])
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, "observations")->textarea() ?>
            </div>
        </div>

        <div class="box-footer">
            <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> '.Yii::t('backend','Crear') : '<i class="fa fa-pencil"></i> '.Yii::t('yii', 'Update'), ['class' => 'btn btn-default btn-flat']) ?>
            <?= Html::a('<i class="fa fa-remove"></i> '.Yii::t('backend','Cancelar'),['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Cancelar')]) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <!-- /.box-body -->
</div>

<?php if(Yii::$app->controller->action->id == 'update') { ?>
<div class="box box-warning box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('backend','Items asociados') ?></h3>

        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
        <!-- /.box-tools -->
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <?= $this->render('_tab_items', [
                'model' => $model,
                'searchModelItems'=>$searchModelItems,
                'dataProviderItems'=>$dataProviderItems
            ])
        ?>
    </div>
    <!-- /.box-body -->
</div>

<?php } ?>

<?php
$url_get_suppliers_info = Url::to(['/customer/get_info'], GlobalFunctions::URLTYPE);
$id_credit = ConditionSale::getIdCreditConditionSale();
$js = <<<JS
// get the form id and set the event
$(document).ready(function(e) {
   
  setcontrols();
    
  $("#proforma-condition_sale_id").change(function() {
	setcontrols();
  });
  
  $("#proforma-customer_id").change(function() 
  {
    var id_value = $(this).val();
    
    if(id_value != '' && id_value != null)
    {
        $.ajax({
            type: "GET",
            url : "$url_get_suppliers_info?id="+id_value,     
            success : function(response) 
            {
                var condition_sale_id = parseInt(response.condition_sale_id);
                var credit_days_id = parseInt(response.credit_days_id);
                $("#proforma-condition_sale_id").val(condition_sale_id).trigger("change");	
                if(credit_days_id !== 0)
                {
                    $("#proforma-credit_days_id").val(credit_days_id).trigger("change");	
                }
                else 
                {
                    $("#proforma-credit_days_id").val("").trigger("change");	
                }
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

  function setcontrols()  {		
 	 if ($("#proforma-condition_sale_id").val() == "$id_credit")
     {
		$("#proforma-credit_days_id").attr('disabled', false);
     }
	 else
	 {		
		$("#proforma-credit_days_id").attr('disabled', true);			
	 }
  }

});
JS;
$this->registerJs($js);
?>


