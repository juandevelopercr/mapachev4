
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
use backend\models\business\Supplier;
use backend\models\nomenclators\BranchOffice;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model backend\models\business\XmlImported */
/* @var $form yii\widgets\ActiveForm */

$this->title = Yii::t('backend', 'Importar XML');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Entradas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box-body">
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <?php if(GlobalFunctions::getRol() !== User::ROLE_FACTURADOR){ ?>
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
        <?php } ?>

        <div class="col-md-6">
            <?= $form->field($model,'xml_file')->fileInput(['class'=>'form-control'])->label(Yii::t('backend','Fichero XML')) ?>
        </div>
    </div>
</div>


<div class="box-footer">
    <?= Html::submitButton('<i class="fa fa-upload"></i> '.Yii::t('backend','Importar') , ['class' => 'btn btn-default btn-flat','name'=>'btn_submit']) ?>
    <?= Html::a('<i class="fa fa-remove"></i> '.Yii::t('backend','Cancelar'),['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Cancelar')]) ?>
</div>
<?php ActiveForm::end(); ?>

<div class="row">
    <div class="col-md-12">
        <?php if(Yii::$app->request->post()) { ?>
            <div class="bg-gray color-palette" style="padding: 10px;">
                <h4><i class="icon fa fa-warning"></i> Proveedor no existente</h4>
                <?php
                $new_supplier = new Supplier([
                    'identification' => $model->array_xml['Emisor']['Identificacion']['Numero'],
                    'name' => $model->array_xml['Emisor']['Nombre'],
                    'phone' => $model->array_xml['Emisor']['Telefono']['CodigoPais'].' '.$model->array_xml['Emisor']['Telefono']['NumTelefono'],
                    'entry_date' => date('Y-m-d'),
                ]);
                echo '<div class="row">';
                    echo '<div class="col-md-4"><b>Nombre: </b>'.$new_supplier->name.'</div>';
                    echo '<div class="col-md-4"><b>Identificación: </b>'.$new_supplier->identification.'</div>';
                    echo '<div class="col-md-4"><b>Teléfono: </b>'.$new_supplier->phone.'</div>';
                echo '</div><br>';

                echo Html::a('<i class="fa fa-plus"></i> '.Yii::t('backend','Crear proveedor'),['supplier/create','pre_model'=>urlencode(serialize($new_supplier)),'return_import' => 1],['class'=>'btn btn-flat btn-primary','title'=>'Crear proveedor', 'data' => ['method' => 'post']]);
                ?>
            </div>
        <?php } ?>
    </div>
</div>

