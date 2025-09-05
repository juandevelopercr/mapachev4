<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\builder\Form;
use kartik\widgets\FileInput;
use kartik\switchinput\SwitchInput;
use dosamigos\ckeditor\CKEditor;
use kartik\date\DatePicker;
use kartik\number\NumberControl;
use common\models\GlobalFunctions;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use backend\models\business\Product;
use common\models\User;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\UtilsConstants;
use backend\models\business\SectorLocation;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Adjustment */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box-body">
<?php
 $type = (int)$model->type;
 $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-5">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'consecutive')->textInput(['maxlength' => true,'readonly' => true]) ?>
                </div>

                <div class="col-md-6">
                    <?php

                    if($type == UtilsConstants::ADJUSTMENT_TYPE_TRANFER) {
                        $label_quantity = Yii::t('backend','Cantidad a trasladar');
                    }
                    elseif($type == UtilsConstants::ADJUSTMENT_TYPE_DECREASE) {
                        $label_quantity = Yii::t('backend','Cantidad a mermar');
                    }
                    elseif($type == UtilsConstants::ADJUSTMENT_TYPE_ADJUSTMENT) {
                        $label_quantity = Yii::t('backend','Cantidad a ajustar');
                    }
                    else
                    {
                        $label_quantity = Yii::t('backend','Cantidad ingresada');
                    }

                    ?>
                    <?=
                    $form->field($model, "entry_quantity")->widget(NumberControl::classname(), [
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 2
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ->label($label_quantity)
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?=
                    $form->field($model, "product_id")->widget(Select2::classname(), [
                        "data" => Product::getSelectMap(),
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
                <div class="col-md-12">
                    <?php

                    if($type == UtilsConstants::ADJUSTMENT_TYPE_TRANFER) {
                        $label_sector_location = Yii::t('backend','Ubicación origen');
                    }
                    else
                    {
                        $label_sector_location = Yii::t('backend','Ubicación');
                    }
                    ?>
                    <?=
                    $form->field($model, "origin_sector_location_id")->widget(Select2::classname(), [
                        "data" => SectorLocation::getSelectMap(),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple"=>false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ])->label($label_sector_location);
                    ?>
                </div>
            </div>
            <?php if($type == UtilsConstants::ADJUSTMENT_TYPE_TRANFER) { ?>
                <div class="row">
                    <div class="col-md-12">
                        <?=
                        $form->field($model, "target_sector_location_id")->widget(Select2::classname(), [
                            "data" => SectorLocation::getSelectMap(),
                            "language" => Yii::$app->language,
                            "options" => ["placeholder" => "----", "multiple"=>false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                        ])
                        ?>
                    </div>
                </div>
            <?php  } ?>

        </div>
        <div class="col-md-7">
            <?=
            $form->field($model, "observations")->widget(CKEditor::className(), [
                "preset" => "custom",
                "clientOptions" => [
                    "toolbar" => GlobalFunctions::getToolBarForCkEditor(),
                ],
            ])
            ?>
        </div>
    </div>

    <div class="row">

    </div>



</div>
<div class="box-footer">
    <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> '.Yii::t('backend','Crear') : '<i class="fa fa-pencil"></i> '.Yii::t('yii', 'Update'), ['class' => 'btn btn-default btn-flat']) ?>
    <?= Html::a('<i class="fa fa-remove"></i> '.Yii::t('backend','Cancelar'),['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Cancelar')]) ?>
</div>
<?php ActiveForm::end(); ?>

