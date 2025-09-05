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
use common\models\User;
use backend\models\nomenclators\Boxes;
use backend\models\nomenclators\BranchOffice;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\business\CashRegister */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box-body">
    <?php
    $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
    <div class="row">
        <div class="col-md-5">
            <div class="row">
                <div class="col-md-6">
                    <?=
                    $form->field($model, "branch_office_id")->widget(Select2::classname(), [
                        "disabled"=>GlobalFunctions::getRol() === User::ROLE_AGENT,
                        "data" => BranchOffice::getSelectMap(),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple" => false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ]);
                    ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'box_id')->widget(DepDrop::classname(), [
                        "disabled"=>GlobalFunctions::getRol() === User::ROLE_AGENT,
                        'type' => DepDrop::TYPE_SELECT2,
                        'data' => ($model->branch_office_id !== '') ? Boxes::getSelectMap($model->branch_office_id) : array(),
                        'options' => ['placeholder' => "----"],
                        'select2Options' => ['pluginOptions' => ['allowClear' => true]],
                        'pluginOptions' => [
                            'depends' => ['cashregister-branch_office_id'],
                            'url' => Url::to(['/util/get-boxes'], GlobalFunctions::URLTYPE),
                            'params' => ['input-type-1', 'input-type-2']
                        ]
                    ]);
                    ?>
                </div>
                <div class="col-md-6">
                    <?=
                    $form->field($model, "seller_id")->widget(Select2::classname(), [
                        "data" => User::getSelectMapAgents(),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple" => false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ]);
                    ?>
                </div>
                <div class="col-md-6">
                    <?=
                    $form->field($model, "opening_date")->widget(DateControl::classname(), [
                        "type" => DateControl::FORMAT_DATE
                    ])
                    ?>
                </div>                
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'opening_time')->textInput() ?>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="row">
                <div class="col-md-12">
                    <?= $this->render('_cajaEditForm', ['movimiento'=>$movimiento, 'movimiento_detail' => $movimiento_detail]) ?>
                </div>
            </div>
        </div>
    </div>

</div>
<div class="box-footer">
    <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> ' . Yii::t('backend', 'Abrir Caja') : '<i class="fa fa-pencil"></i> ' . Yii::t('yii', 'Editar Caja'), ['class' => 'btn btn-default btn-flat', 'confirm'=> 'Are you Sure']) ?>
    <?= Html::a('<i class="fa fa-remove"></i> ' . Yii::t('backend', 'Cancelar'), ['arqueo', 'box_id'=>$model->box_id], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Cancelar')]) ?>
</div>
<?php ActiveForm::end(); ?>