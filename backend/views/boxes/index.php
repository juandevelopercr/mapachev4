<?php

use yii\helpers\Html;
use common\widgets\GridView;
use mdm\admin\components\Helper;
use yii\web\View;
use yii\helpers\Url;
use backend\components\Footer_Bulk_Delete;
use backend\components\Custom_Settings_Column_GridView;
use common\models\GlobalFunctions;
use yii\helpers\BaseStringHelper;
use backend\models\nomenclators\BranchOffice;
use backend\models\business\Invoice;
use common\models\User;

/* @var $this yii\web\View */
/* @var $searchModel app\models\business\PointsSaleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/' . $this->context->uniqueId . '/';
$this->title = Yii::t('backend', 'Cajas');
$this->params['breadcrumbs'][] = $this->title;

$create_button = '';
?>

<?php
if (Helper::checkRoute($controllerId . 'create')) {
    if (GlobalFunctions::getRol() === User::ROLE_SUPERADMIN || GlobalFunctions::getRol() === User::ROLE_ADMIN)
        $create_button = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('backend', 'Crear'), ['create'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Crear') . ' ' . Yii::t('backend', 'Caja')]);
}

$custom_buttons = [
    'arqueo' => function ($url, $model) {
        $options = [
            'title' => Yii::t('backend', 'Arqueo de Caja'),
            'class' => 'btn btn-xs btn-default btn-flat',
            'aria-label' => Yii::t('backend', 'Arqueo de Caja'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',
            'style' => 'color:blue',
        ];
        if ($model->is_point_sale == 1)
            return Html::a('<i class="glyphicon glyphicon-usd"></i>', ['/cash-register/arqueo', 'box_id' => $model->id], $options);
    },
    'view' => function ($url, $model) {
        $options = [
            'title' => Yii::t('backend', 'Actualizar'),
            'class' => 'btn btn-xs btn-default btn-flat',
            'aria-label' => Yii::t('backend', 'Actualizar'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',
        ];
        if (GlobalFunctions::getRol() === User::ROLE_ADMIN)
            return Html::a('<i class="glyphicon glyphicon-eye-open"></i>', ['/boxes/view', 'id' => $model->id], $options);
    },
    'update' => function ($url, $model) {
        $options = [
            'title' => Yii::t('backend', 'Actualizar'),
            'class' => 'btn btn-xs btn-default btn-flat',
            'aria-label' => Yii::t('backend', 'Actualizar'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',
        ];
        if (GlobalFunctions::getRol() === User::ROLE_ADMIN)
            return Html::a('<i class="glyphicon glyphicon-pencil"></i>', ['/boxes/update', 'id' => $model->id], $options);
    },
    'delete' => function ($url, $model){
        $options = [
            'title' => Yii::t('backend', 'Eliminar'),
            'class' => 'btn btn-xs btn-danger btn-flat',
            'aria-label' => Yii::t('backend', 'Eliminar'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',
            'data-confirm' => Yii::t('backend', '¿Seguro desea eliminar este elemento?'),

        ];
        if (Invoice::getCountInvoiceByBox($model->branch_office_id, $model->id) == 0 || GlobalFunctions::getRol() === User::ROLE_ADMIN)
            return Html::a('<i class="glyphicon glyphicon-trash"></i>', ['/boxes/delete', 'id' => $model->id], $options);
    },
];

$custom_elements_gridview = new Custom_Settings_Column_GridView($create_button, $dataProvider, ['arqueo', 'view', 'update', 'delete'], $custom_buttons);
?>


<div class="box-body">
    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    $panel = $custom_elements_gridview->getPanel();
    $panel['after'] = '';
    ?>
    <?= GridView::widget([
        'id' => 'grid',
        'dataProvider' => $dataProvider,
        'pjax' => true,
        'pjaxSettings' => [
            'neverTimeout' => true,
            'options' => [
                'enablePushState' => false,
                'scrollTo' => false,
            ],
        ],
                'autoXlFormat'=>true,
        'responsiveWrap' => false,
        'floatHeader' => true,
        'floatHeaderOptions' => [
            'position' => 'absolute',
            'top' => 50
        ],
        'hover' => true,
        'pager' => [
            'firstPageLabel' => Yii::t('backend', 'Primero'),
            'lastPageLabel' => Yii::t('backend', 'Último'),
        ],
        'hover' => true,
        'persistResize' => true,
        'filterModel' => $searchModel,
        'as filterBehavior' => \thrieu\grid\FilterStateBehavior::className(),	
        'columns' => [

            $custom_elements_gridview->getSerialColumn(),
            [
                'attribute' => 'branch_office_id',
                'format' => 'html',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => BranchOffice::getSelectMap(),
                'filterWidgetOptions' => [
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                    'options' => ['multiple' => false],
                ],
                'value' => 'branchOffice.name',
                'filterInputOptions' => ['placeholder' => '------'],
                'hAlign' => 'center',
            ],
            [
                'attribute' => 'numero',
                'contentOptions' => ['class' => 'kv-align-center kv-align-middle'],
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) {
                    return $data->numero;
                }
            ],
            [
                'attribute' => 'name',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) {
                    return $data->name;
                }
            ],
            [
                'attribute'=> 'is_point_sale',
                'contentOptions' => ['class' => 'kv-align-center kv-align-middle'],
                'hAlign' => 'center',
                'value'=> function($model){
                    return $model->is_point_sale == 1 ? 'Si': 'No';
                },
                'format'=> 'html',
            ], 

            $custom_elements_gridview->getActionColumn(),

            $custom_elements_gridview->getCheckboxColumn(),
        ],

        'toolbar' =>  $custom_elements_gridview->getToolbar(),

        'panel' => $panel,

        'toggleDataOptions' => $custom_elements_gridview->getTogleDataOptions(),
    ]); ?>
</div>

<?php
$url = Url::to([$controllerId . 'multiple_delete'], GlobalFunctions::URLTYPE);
$js = Footer_Bulk_Delete::getFooterBulkDelete($url);
$this->registerJs($js, View::POS_READY);
?>