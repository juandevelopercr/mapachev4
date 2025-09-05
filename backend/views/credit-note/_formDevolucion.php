<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\switchinput\SwitchInput;
use kartik\number\NumberControl;
use common\models\GlobalFunctions;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use backend\models\nomenclators\BranchOffice;
use backend\models\business\Customer;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use common\models\User;
use backend\models\nomenclators\PaymentMethod;
use yii\helpers\Url;
use backend\models\nomenclators\RouteTransport;
use backend\models\nomenclators\ReferenceCode;

/* @var $this yii\web\View */
/* @var $model backend\models\business\CreditNote */
/* @var $form yii\widgets\ActiveForm */
/* @var $searchModelItems \backend\models\business\ItemCreditNoteSearch */
/* @var $dataProviderItems yii\data\ActiveDataProvider */

?>
<?php
$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<div class="box box-default box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('backend','Devolución de Mercancia') ?></h3>

        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
        <!-- /.box-tools -->
    </div>
    <!-- /.box-header -->
    <div class="box-body">

        <?php if(Yii::$app->controller->action->id == 'create') { ?>
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-warning"></i> Nota!</h4>
             Está acción creará un duplicado de la factura seleccionada. Presione el botón crear y realice las modificaciones necesarias. La nota de crédito debe enviarse nuevamente a Hacienda para modificar la factura de referencia
        </div>
        <?php } ?>

        <div class="row">

            <?php
            if(Yii::$app->controller->action->id === 'update')
            {
                echo '<div class="col-md-4">';
                echo $form->field($model, 'consecutive')->textInput(['maxlength' => true, 'readonly'=>true]);
                echo '</div>';
            }
            ?>

            <div class="col-md-2">
                <?=
                $form->field($model, "status")->widget(Select2::classname(), [
                    "data" => UtilsConstants::getInvoiceStatusSelectType(),
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
                $form->field($model, "branch_office_id")->widget(Select2::classname(), [
                    "data" => BranchOffice::getSelectMap(),
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

            <div class="col-md-offset-1 col-md-2">
                <?=
                $form->field($model, "collector_id")->widget(Select2::classname(), [
                    "data" => User::getSelectMapAgents(),
                    "language" => Yii::$app->language,
                    "options" => ["placeholder" => "----", "multiple"=>false],
                    "pluginOptions" => [
                        "allowClear" => true
                    ],
                ]);
                ?>
            </div>
            */
            ?>
            <div class="col-md-2">
                <?=
                $form->field($model, "sellers")->widget(Select2::classname(), [
                    "data" => User::getSelectMapAgents(),
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
            <div class="col-md-offset-1 col-md-2">
                <?=
                $form->field($model, "collectors")->widget(Select2::classname(), [
                    "data" => User::getSelectMapAgents(),
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

            <div class="col-md-offset-1 col-md-3">
                <?=
                $form->field($model, "route_transport_id")->widget(Select2::classname(), [
                    "data" => RouteTransport::getSelectMap(),
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
                $form->field($model, "status_hacienda")->widget(Select2::classname(), [
                    "data" => UtilsConstants::getHaciendaStatusSelectType(),
                    'disabled' => true,
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

            <div class="col-md-offset-1 col-md-5">
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
        </div>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, "observations")->textarea() ?>
            </div>
        </div>

        <div class="row" style="margin-bottom:20px;">
            <div class="col-md-12">
                <fieldset id="pedido_minimo_fieldset" style="width: 100%; border: 1px solid #C0C0C0; margin-top:15px">
                    <legend style="width: auto; margin: 0; border: 0; padding-right: 1%; padding-left: 1%; font-size: 14px; font-weight: bold">Datos de la Factura que modifica</legend>

                    <div class="col-md-6">
                        <?= $form->field($model, 'reference_number')->textInput(['maxlength'=>true, 'readonly'=>true]) ?>
                    </div>
                    <div class="col-md-6">
                        <?=
                        $form->field($model, "reference_code")->widget(Select2::classname(), [
                            "data" => ReferenceCode::getSelectMapStaticDevolucion(),
                            "language" => Yii::$app->language,
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                        ]);
                        ?>
                    </div>
                    <?php
                    /*
                    <div class="col-md-6">
                        <?=
                        $form->field($model, "reference_emission_date")->widget(DateControl::classname(), [
                            "type" => DateControl::FORMAT_DATE
                        ])
                        ?>
                    </div>
                    */
                    ?>
                    <div class="col-md-6">
                        <?= $form->field($model, 'reference_reason')->textInput(['maxlength'=>true]) ?>
                    </div>
                </fieldset>
            </div>
        </div>


        <div class="box-footer">
            <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> '.Yii::t('backend','Crear') : '<i class="fa fa-pencil"></i> '.Yii::t('yii', 'Update'), ['class' => 'btn btn-default btn-flat']) ?>
            <?= Html::a('<i class="fa fa-remove"></i> '.Yii::t('backend','Cancelar'),['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Cancelar')]) ?>
        </div>        
    </div>
    <!-- /.box-body -->
</div>


<div class="box box-warning box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('backend','Introduzca las cantidades a devolver') ?></h3>

        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
        <!-- /.box-tools -->
    </div>
    <!-- /.box-header -->
    <div class="box-body">

        <?= $this->render('_tab_items_devolucion', [
            'model' => $model,
            'items'=>$items,
        ])
        ?>

        <div class="box-footer pull-right">
            <?= Html::submitButton('<i class="fa fa-save info"></i> '.Yii::t('yii', 'Crear devolución'), ['class' => 'btn btn-default btn-flat', 'name'=>'btn-devolucion']) ?>
        </div>

    </div>
    <!-- /.box-body -->
</div>

<?php ActiveForm::end(); ?>     

<?php
$url_get_customers_info = Url::to(['/customer/get_info'], GlobalFunctions::URLTYPE);
$id_credit = ConditionSale::getIdCreditConditionSale();
$js = <<<JS
// get the form id and set the event
$(document).ready(function(e) {
   
  setcontrols();
    
  $("#creditnote-condition_sale_id").change(function() {
	setcontrols();
  });
  
  $("#creditnote-customer_id").change(function() 
  {
    var id_value = $(this).val();
    
    if(id_value != '' && id_value != null)
    {
        $.ajax({
            type: "GET",
            url : "$url_get_customers_info?id="+id_value,     
            success : function(response) 
            {
                var condition_sale_id = parseInt(response.condition_sale_id);
                var credit_days_id = parseInt(response.credit_days_id);
                var sellers = response.sellers;
                var invoice_type = parseInt(response.invoice_type);
                
                $("#creditnote-condition_sale_id").val(condition_sale_id).trigger("change");	
                
                if(credit_days_id !== 0)
                {
                    $("#creditnote-credit_days_id").val(credit_days_id).trigger("change");	
                }
                else 
                {
                    $("#creditnote-credit_days_id").val("").trigger("change");	
                }

                if(sellers)
                {
                    $("#creditnote-sellers").val(sellers).trigger("change");	
                }
                else 
                {
                    $("#creditnote-sellers").val("").trigger("change");	
                }   


                $("#creditnote-invoice_type").val(invoice_type).trigger("change");
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
 	 if ($("#creditnote-condition_sale_id").val() == "$id_credit")
     {
		$("#creditnote-credit_days_id").attr('disabled', false);
     }
	 else
	 {		
		$("#creditnote-credit_days_id").attr('disabled', true);			
	 }
  }

});
JS;
$this->registerJs($js);
?>


