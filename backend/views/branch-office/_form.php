<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use wbraganca\dynamicform\DynamicFormWidget;
use kartik\widgets\SwitchInput;
use backend\models\nomenclators\UtilsConstants;
use kartik\select2\Select2;
use backend\models\business\Sector;


/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\BranchOffice */
/* @var $form yii\widgets\ActiveForm */

$js = '

jQuery(".dynamicform_wrapper").on("afterInsert", function(e, item) {
    jQuery(".dynamicform_wrapper .panel-title-sector").each(function(index) {
        jQuery(this).html("Sector No." + (index + 1))
    });
});

jQuery(".dynamicform_wrapper").on("afterDelete", function(e) {
    jQuery(".dynamicform_wrapper .panel-title-sector").each(function(index) {
        jQuery(this).html("Sector No." + (index + 1))
    });
});
';

$this->registerJs($js);
?>

<div class="box-body">
<?php 
 $form = ActiveForm::begin(['id' => 'dynamic-form','options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'description')->textarea() ?>
        </div>
        <div class="col-md-2">
            <?=
            $form->field($model,"status")->widget(SwitchInput::classname(), [
                "type" => SwitchInput::CHECKBOX,
                "pluginOptions" => [
                    "onText"=> Yii::t("backend","Activo"),
                    "offText"=> Yii::t("backend","Inactivo")
                ]
            ])
            ?>
        </div>
    </div>

    <?php DynamicFormWidget::begin([
        'widgetContainer' => 'dynamicform_wrapper',
        'widgetBody' => '.container-items',
        'widgetItem' => '.sector-item',
        //'limit' => 10,
        'min' => 1,
        'insertButton' => '.add-sector',
        'deleteButton' => '.remove-sector',
        'model' => $modelsSector[0],
        'formId' => 'dynamic-form',
        'formFields' => [
            'code',
            'name'
        ],
    ]); ?>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Sectores</th>
            <th style="width: 50%;">Ubicaciones</th>
            <th class="text-center" style="width: 90px;">
                <button type="button" class="add-sector btn btn-success btn-xs"><span class="fa fa-plus"></span></button>
            </th>
        </tr>
        </thead>
        <tbody class="container-items">
        <?php foreach ($modelsSector as $indexSector => $modelSector): ?>
            <tr class="sector-item">
                <td class="vcenter">
                    <?php
                    // necessary for update action.
                    if (! $modelSector->isNewRecord) {
                        echo Html::activeHiddenInput($modelSector, "[{$indexSector}]id");
                    }
                    ?>
                    <div class="row">

                        <div class="col-md-12">
                            <?= $form->field($modelSector, "[{$indexSector}]name")->textInput(['maxlength' => true,'class'=>'form-control']) ?>
                        </div>

                        <div class="col-md-5">
                            <?= $form->field($modelSector, "[{$indexSector}]code")->widget(Select2::classname(), [
                                "data" => UtilsConstants::getAlphabetCodesSelectMap(),
                                "language" => Yii::$app->language,
                                "options" => ["placeholder" => "----", "multiple"=>false],
                                "pluginOptions" => [
                                    "allowClear" => true
                                ],
                            ]) ?>
                        </div>
                    </div>
                </td>
                <td>
                    <?= $this->render('_form_locations', [
                        'form' => $form,
                        'indexSector' => $indexSector,
                        'modelsSectorLocation' => $modelsSectorLocation[$indexSector],
                    ]) ?>
                </td>
                <td class="text-center vcenter" style="width: 90px; verti">
                    <button type="button" class="remove-sector btn btn-danger btn-xs"><span class="fa fa-minus"></span></button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php DynamicFormWidget::end(); ?>
    
</div>
<div class="box-footer">
    <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> '.Yii::t('backend','Crear') : '<i class="fa fa-pencil"></i> '.Yii::t('yii', 'Update'), ['class' => 'btn btn-default btn-flat']) ?>
    <?= Html::a('<i class="fa fa-remove"></i> '.Yii::t('backend','Cancelar'),['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Cancelar')]) ?>
</div>
<?php ActiveForm::end(); ?>

<?php
$this->registerJsFile(Yii::$app->getUrlManager()->getBaseUrl().'/js/dynamicform.js',['depends'=>[\yii\web\JqueryAsset::className()], 'position'=>\yii\web\View::POS_END]);

$js_2 =<<< JS

    $(".dynamicform_wrapper").on("afterInsert", function(e, item) {
        $(item).find('input,textarea').each(function(index,element){
           $(element).val('');
        });
        
        $(item).find('select').each(function(index,element){
            $(element).val(null).trigger("change");
        });
    });

    $(".dynamicform_inner").on("afterInsert", function(e, item) {
        $(item).find('input').each(function(index,element){
           $(element).val('');
        });
    });

JS;
$this->registerJs($js_2 , \yii\web\View::POS_READY);
?>