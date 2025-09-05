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
use backend\models\nomenclators\IdentificationType;
use backend\models\nomenclators\Province;
use backend\models\nomenclators\Canton;
use backend\models\nomenclators\Disctrict;
use yii\web\JsExpression;
use backend\models\nomenclators\Cabys;
use common\models\User;
use backend\models\nomenclators\PaymentMethod;
use yii\helpers\Url;
use kartik\depdrop\DepDrop;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Invoice */
/* @var $form yii\widgets\ActiveForm */
/* @var $searchModelItems \backend\models\business\ItemInvoiceSearch */
/* @var $dataProviderItems yii\data\ActiveDataProvider */
?>
<div class="modal-body">
    <form onsubmit="customerAdd(); return false;" id="md_frm1" class="form-vertical">
        <div class="row">
            <div class="col-md-12">
                <?php
                // The controller action that will render the list
                $url_cabys = Url::to(['cabys/cabys_list'], GlobalFunctions::URLTYPE);

                // Get the initial values
                $init_value_cabys = empty($model->cabys_id) ? '' : Cabys::getLabelSelectById($model->cabys_id);

                echo $form->field($model, 'customern')->widget(Select2::classname(), [
                    'initValueText' => $init_value_cabys, // set the initial display text
                    "language" => Yii::$app->language,
                    "options" => ["placeholder" => "----", "multiple" => false],
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

        <div class="form-group pull-right" style="margin-top:15px;">
            <button type="submit" class="btn btn-primary">Seleccionar Cliente</button>
        </div>
    </form>
</div>