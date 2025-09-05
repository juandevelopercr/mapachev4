<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\switchinput\SwitchInput;
use kartik\number\NumberControl;
use common\models\GlobalFunctions;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use backend\models\business\Supplier;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use common\models\User;
use backend\models\nomenclators\PaymentMethod;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\business\PaymentOrder */
/* @var $form yii\widgets\ActiveForm */
?>
<?= GlobalFunctions::showModalHtmlContent(Yii::t('backend', 'Imagen'), 'modal-lg') ?>

<div class="box box-warning box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('backend', 'Orden de compra') ?></h3>

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
                <?= $form->field($model, 'number')->textInput(['maxlength' => true, 'readonly' => true]) ?>
            </div>

            <div class="col-md-4 col-md-offset-2">
                <?=
                $form->field($model, "request_date")->widget(DateControl::classname(), [
                    "type" => DateControl::FORMAT_DATE
                ])
                ?>
            </div>
            <div class="col-md-4">
                <?=
                $form->field($model, "require_date")->widget(DateControl::classname(), [
                    "type" => DateControl::FORMAT_DATE
                ])
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?=
                $form->field($model, "currency_id")->widget(Select2::classname(), [
                    "data" => Currency::getSelectMap(),
                    "language" => Yii::$app->language,
                    "options" => ["placeholder" => "----", "multiple" => false],
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
                <?=
                $form->field($model, "status_payment_order_id")->widget(Select2::classname(), [
                    'disabled' => (GlobalFunctions::getRol() === User::ROLE_FACTURADOR || Yii::$app->controller->action->id == 'create'),
                    "data" => UtilsConstants::getStatusPaymentOrderSelectMap(),
                    "language" => Yii::$app->language,
                    "options" => ["placeholder" => "----", "multiple" => false],
                    "pluginOptions" => [
                        "allowClear" => true
                    ],
                ]);
                ?>
            </div>
            <div class="col-md-2">
                <?=
                $form->field($model, "payout_status")->widget(Select2::classname(), [
                    'disabled' => (Yii::$app->controller->action->id == 'create'),
                    "data" => UtilsConstants::getPayoutStatusSelectType(),
                    "language" => Yii::$app->language,
                    "options" => ["placeholder" => "----", "multiple" => false],
                    "pluginOptions" => [
                        "allowClear" => true
                    ],
                ]);
                ?>
            </div>
            <div class="col-md-2">
                <?=
                $form->field($model, "is_editable")->widget(SwitchInput::classname(), [
                    'disabled' => (GlobalFunctions::getRol() !== User::ROLE_SUPERADMIN),
                    "type" => SwitchInput::CHECKBOX,
                    "pluginOptions" => [
                        "onText" => Yii::t("backend", "SI"),
                        "offText" => Yii::t("backend", "NO")
                    ]
                ])
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?=
                $form->field($model, "supplier_id")->widget(Select2::classname(), [
                    "data" => Supplier::getSelectMap(),
                    "language" => Yii::$app->language,
                    "options" => ["placeholder" => "----", "multiple" => false],
                    "pluginOptions" => [
                        "allowClear" => true
                    ],
                ]);
                ?>
            </div>
            <div class="col-md-4">
                <?=
                $form->field($model, "condition_sale_id")->widget(Select2::classname(), [
                    "data" => ConditionSale::getSelectMap(),
                    "language" => Yii::$app->language,
                    "options" => ["placeholder" => "----", "multiple" => false],
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
                    "options" => ["placeholder" => "----", "multiple" => false],
                    "pluginOptions" => [
                        "allowClear" => true
                    ],
                ]);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?=
                $form->field($model, "payment_methods")->widget(Select2::classname(), [
                    "data" => PaymentMethod::getSelectMap(),
                    "language" => Yii::$app->language,
                    'maintainOrder' => true,
                    "options" => [
                        "placeholder" => "----",
                        "multiple" => true
                    ],
                    "pluginOptions" => [
                        "allowClear" => true
                    ]
                ]);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?=
                $form->field($model, "observations")->textarea()
                ?>
            </div>
        </div>

        <div class="box-footer">
            <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> ' . Yii::t('backend', 'Crear') : '<i class="fa fa-pencil"></i> ' . Yii::t('yii', 'Update'), ['class' => 'btn btn-default btn-flat']) ?>
            <?= Html::a('<i class="fa fa-remove"></i> ' . Yii::t('backend', 'Cancelar'), ['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Cancelar')]) ?>
        </div>
        <?php ActiveForm::end(); ?>

    </div>
    <!-- /.box-body -->
</div>
<?php if (Yii::$app->controller->action->id == 'update') { ?>
    <div class="box box-warning box-solid">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('backend', 'Items asociados') ?></h3>

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
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider
            ])
            ?>
        </div>
        <!-- /.box-body -->
    </div>
<?php } ?>

<?php
$url_get_suppliers_info = Url::to(['/supplier/get_info'], GlobalFunctions::URLTYPE);
$id_credit = ConditionSale::getIdCreditConditionSale();

$js = <<<JS
// get the form id and set the event
$(document).ready(function(e) {
   
  setcontrols();
    
  $("#paymentorder-condition_sale_id").change(function() {
	setcontrols();
  });
    
  $("#paymentorder-supplier_id").change(function() 
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
                $("#paymentorder-condition_sale_id").val(condition_sale_id).trigger("change");	
                if(credit_days_id !== 0)
                {
                    $("#paymentorder-credit_days_id").val(credit_days_id).trigger("change");	
                }
                else 
                {
                    $("#paymentorder-credit_days_id").val("").trigger("change");	
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

  function setcontrols() {		
 	 if ($("#paymentorder-condition_sale_id").val() == "$id_credit")
     {
		$("#paymentorder-credit_days_id").attr('disabled', false);
     }
	 else
	 {		
		$("#paymentorder-credit_days_id").attr('disabled', true);			
	 }
  }
  
});
JS;
$this->registerJs($js);
?>