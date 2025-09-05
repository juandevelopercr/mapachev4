<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\file\FileInput;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use yii\helpers\Url;
use kartik\daterange\DateRangePicker;
use backend\models\business\Customer;
use backend\models\nomenclators\Banks;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use backend\models\nomenclators\InventoryType;
use backend\models\nomenclators\Family;
use backend\models\nomenclators\Category;
use backend\models\nomenclators\Country;
use kartik\depdrop\DepDrop;
use common\models\User;
use backend\models\nomenclators\Zone;


/* @var $this yii\web\View */
/* @var $searchModel backend\modules\facturacion\models\FacturasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Reporte de Consolidación de Pagos';
$this->params['breadcrumbs'][] = $this->title;

// add conditions that should always apply here
?>
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-body">
                <?php
                $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);
                ?>
                <input type="hidden" name="module_id" value='gridview' />
                <input type="hidden" name="export_filetype" value="xls" />
                <input type="hidden" name="export_filename" value="liquidacion-pagos" id="inpFileName" />
                <input type="hidden" name="export_mime" value="application/vnd.ms-excel" />
                <input type="hidden" name="export_config" value='{"worksheet":"liquidacion-pagos","cssFile":""}' />
                <input type="hidden" name="export_encoding" value="utf-8" />
                <input type="hidden" name="export_bom" value="1" />
                <div class="row">
                    <div class="col-md-3">
                        <?=
                        $form->field($model, "fecha")->widget(DateControl::classname(), [
                            "type" => DateControl::FORMAT_DATE
                        ])
                        ?>
                    </div>
                    <div class="col-md-3">
                        <?=
                        $form->field($model, "cliente")->widget(Select2::classname(), [
                            "data" => Customer::getSelectMap(),
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
                        $form->field($model, "collector")->widget(Select2::classname(), [
                            "data" => User::getSelectMapAgents(),
                            "language" => "es",
                            "options" => ["placeholder" => Yii::t("app", "-- Selecione --"), "multiple" => false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                            'disabled' => false,
                        ]);
                        ?>
                    </div>
                    <div class="col-md-3">
                        <?=
                        $form->field($model, "estado")->widget(Select2::classname(), [
                            "data" => UtilsConstants::getHaciendaStatusSelectType(),
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
                    <?php
                    $mostrar = 'Mostrar Reporte';
                    ?>
                    <div class="form-group pull-right">
                        <?= Html::submitButton("<i class='glyphicon glyphicon-print text-info'></i> " . $mostrar . "", ['class' => 'btn btn-default', 'name' => 'btnguardar']); ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>