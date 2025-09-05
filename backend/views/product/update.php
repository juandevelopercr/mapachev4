<?php

use kartik\tabs\TabsX;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\builder\Form;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Product */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Producto').': '. $model->description;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Productos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->description, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="product-update">

    <?php
    $form = ActiveForm::begin(['id' => 'dynamic-form','options' => ['enctype' => 'multipart/form-data']]);

    $main_data = $this->render('_form', [
        'model' => $model,
        'form' => $form,
    ]);

    $price_data = $this->render('_form_prices', [
        'model' => $model,
        'form' => $form,
    ]);

    $extra_data = $this->render('_form_extra', [
        'model' => $model,
        'form' => $form,
    ]);

    $move_data = $this->render('_tab_move', [
        'dataProvider' => $dataProvider,
    ]);

    echo TabsX::widget([
        'position' => TabsX::POS_ABOVE,
        'encodeLabels' => false,
        'items' => [
            [
                'label' => '<i class="glyphicon glyphicon-list-alt"></i> '.Yii::t('backend', 'Datos Generales'),
                'content' => $main_data,
                'active' => true
            ],

            [
                'label' => '<i class="fa fa-money"></i> '.Yii::t('backend', 'Costos y precios'),
                'content' => $price_data,
                'active' => false
            ],

            [
                'label' => '<i class="fa fa-asterisk"></i> '.Yii::t('backend', 'Datos de hacienda'),
                'content' => $extra_data,
                'active' => false
            ],

            [
                'label' => '<i class="fa fa-archive"></i> '.Yii::t('backend', 'Ubicación física'),
                'content' => $this->render('_tab_location', [
                    'model' => $model,
                    'searchModel' => $searchModelPhysicalLocations,
                    'dataProvider' => $dataProviderPhysicalLocation
                ]),
                'active' => false
            ],

            [
                'label' => '<i class="fa fa-exchange"></i> '.Yii::t('backend', 'Movimientos'),
                'content' => $move_data,
                'active' => false
            ],
        ],
    ]);
    ?>

    <div class="box-footer">
        <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> '.Yii::t('backend','Crear') : '<i class="fa fa-pencil"></i> '.Yii::t('yii', 'Update'), ['class' => 'btn btn-default btn-flat']) ?>
        <?= Html::a('<i class="fa fa-remove"></i> '.Yii::t('backend','Cancelar'),['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Cancelar')]) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
