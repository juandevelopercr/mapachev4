<?php

use backend\models\business\Customer;
use backend\models\nomenclators\Category;
use backend\models\nomenclators\Country;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\Family;
use backend\models\nomenclators\InventoryType;
use backend\models\nomenclators\UtilsConstants;
use common\models\GlobalFunctions;
use kartik\daterange\DateRangePicker;
use kartik\depdrop\DepDrop;
use kartik\file\FileInput;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $searchModel backend\modules\facturacion\models\FacturasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Reporte de Inventario';
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
                <input type="hidden" name="export_filename" value="inventario" id="inpFileName" />
                <input type="hidden" name="export_mime" value="application/vnd.ms-excel" />
                <input type="hidden" name="export_config" value='{"worksheet":"Inventario","cssFile":""}' />
                <input type="hidden" name="export_encoding" value="utf-8" />
                <input type="hidden" name="export_bom" value="1" />
                <div class="row">
                    <div class="col-md-4">
                        <?=
                        $form->field($model, "family")->widget(Select2::classname(), [
                            "data" => Family::getSelectMap(),
                            "language" => Yii::$app->language,
                            "options" => ["placeholder" => "----", "multiple" => false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                        ]);
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'category')->widget(DepDrop::classname(), [
                            'type' => DepDrop::TYPE_SELECT2,
                            'data' => ($model->family > 0) ? Category::getSelectMapSpecific($model->family) : array(),
                            'options' => ['placeholder' => "----"],
                            'select2Options' => ['pluginOptions' => ['allowClear' => true]],
                            'pluginOptions' => [
                                'depends' => ['inventarioreportform-family'],
                                'url' => Url::to(['/util/get_categories'], GlobalFunctions::URLTYPE),
                                'params' => ['input-type-1', 'input-type-2']
                            ]
                        ]);
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?=
                        $form->field($model, "tipo")->widget(Select2::classname(), [
                            "data" => InventoryType::getSelectMap(),
                            "language" => Yii::$app->language,
                            "options" => ["placeholder" => "----", "multiple" => false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                        ]);
                        ?>
                    </div>                
                </div>
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