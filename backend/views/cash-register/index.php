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
/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\CashRegisteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/' . $this->context->uniqueId . '/';
$this->title = Yii::t('backend', 'Arqueo de Caja');
$this->params['breadcrumbs'][] = $this->title;

$create_button = '';
?>

<?php
if (Helper::checkRoute($controllerId . 'create')) {
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
        return Html::a('<i class="glyphicon glyphicon-usd"></i>', ['/cash-register/arqueo', 'id' => $model->id], $options);
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
        return Html::a('<i class="glyphicon glyphicon-pencil"></i>', ['/boxes/update', 'id' => $model->id], $options);
    },
    'delete' => function ($url, $model) {
        $options = [
            'title' => Yii::t('backend', 'Eliminar'),
            'class' => 'btn btn-xs btn-danger btn-flat',
            'aria-label' => Yii::t('backend', 'Eliminar'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',
            'data-confirm' => Yii::t('backend', '¿Seguro desea eliminar este elemento?'),

        ];
        return Html::a('<i class="glyphicon glyphicon-trash"></i>', ['/boxes/delete', 'id' => $model->id], $options);
    },
];

$custom_elements_gridview = new Custom_Settings_Column_GridView($create_button, $dataProvider, ['arqueo', 'view', 'update', 'delete'], $custom_buttons);
?>
<div class="box-body">
    <?php // echo $this->render('_search', ['model' => $searchModel]); 
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
                'attribute'=>'opening_date',
                'value' => function($data){
                    return GlobalFunctions::formatDateToShowInSystem($data->opening_date);
                },
                'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                'hAlign'=>'center',
                'filterType' => GridView::FILTER_DATE_RANGE,
                'filterWidgetOptions' => ([
                    'model' => $searchModel,
                    'attribute' => 'opening_date',
                    'presetDropdown' => false,
                    'convertFormat' => true,
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'd-M-Y'
                        ]
                    ],
                    'pluginEvents' => [
                        'apply.daterangepicker' => 'function(ev, picker) {
                            if($(this).val() == "") {
                                picker.callback(picker.startDate.clone(), picker.endDate.clone(), picker.chosenLabel);
                            }
                        }',
                    ]
                ])
            ],
            [
                'attribute' => 'opening_time',
                'contentOptions' => ['class' => 'kv-align-center kv-align-middle'],
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) {
                    return !empty($data->opening_time) ? date('h:i:s A', strtotime($data->opening_time)): '';
                }
            ],            
            [
                'attribute'=>'closing_date',
                'value' => function($data){
                    return GlobalFunctions::formatDateToShowInSystem($data->closing_date);
                },
                'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                'hAlign'=>'center',
                'filterType' => GridView::FILTER_DATE_RANGE,
                'filterWidgetOptions' => ([
                    'model' => $searchModel,
                    'attribute' => 'closing_date',
                    'presetDropdown' => false,
                    'convertFormat' => true,
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'd-M-Y'
                        ]
                    ],
                    'pluginEvents' => [
                        'apply.daterangepicker' => 'function(ev, picker) {
                            if($(this).val() == "") {
                                picker.callback(picker.startDate.clone(), picker.endDate.clone(), picker.chosenLabel);
                            }
                        }',
                    ]
                ])
            ],    
            [
                'attribute' => 'closing_time',
                'contentOptions' => ['class' => 'kv-align-center kv-align-middle'],
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) {
                    return !empty($data->closing_time) ? date('h:i:s A', strtotime($data->closing_time)): '';
                }
            ],   
            [
                'attribute' => 'initial_amount',
                'label' => Yii::t('backend', 'Monto inicial'),
                'contentOptions' => ['class' => 'kv-align-right kv-align-middle'],
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM,
                'filterType' => GridView::FILTER_NUMBER,
                'filterWidgetOptions' => [
                    'maskedInputOptions' => [
                        'allowMinus' => false,
                        'groupSeparator' => '.',
                        'radixPoint' => ',',
                        'digits' => 2
                    ],
                    'displayOptions' => ['class' => 'form-control kv-monospace'],
                    'saveInputContainer' => ['class' => 'kv-saved-cont']
                ],
                'value' => function ($data) {
                    return GlobalFunctions::formatNumber($data->initial_amount, 2);
                },
                'format' => 'html',
            ],  
            [
                'attribute' => 'end_amount',
                'label' => Yii::t('backend', 'Monto final'),
                'contentOptions' => ['class' => 'kv-align-right kv-align-middle'],
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM,
                'filterType' => GridView::FILTER_NUMBER,
                'filterWidgetOptions' => [
                    'maskedInputOptions' => [
                        'allowMinus' => false,
                        'groupSeparator' => '.',
                        'radixPoint' => ',',
                        'digits' => 2
                    ],
                    'displayOptions' => ['class' => 'form-control kv-monospace'],
                    'saveInputContainer' => ['class' => 'kv-saved-cont']
                ],
                'value' => function ($data) {
                    return GlobalFunctions::formatNumber($data->end_amount, 2);
                },
                'format' => 'html',
            ], 
            [
                'attribute' => 'total_sales',
                'label' => Yii::t('backend', 'Total de Ventas'),
                'contentOptions' => ['class' => 'kv-align-right kv-align-middle'],
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM,
                'filterType' => GridView::FILTER_NUMBER,
                'filterWidgetOptions' => [
                    'maskedInputOptions' => [
                        'allowMinus' => false,
                        'groupSeparator' => '.',
                        'radixPoint' => ',',
                        'digits' => 2
                    ],
                    'displayOptions' => ['class' => 'form-control kv-monospace'],
                    'saveInputContainer' => ['class' => 'kv-saved-cont']
                ],
                'value' => function ($data) {
                    return GlobalFunctions::formatNumber($data->total_sales, 2);
                },
                'format' => 'html',
            ],                                                        

            $custom_elements_gridview->getActionColumn(),

            $custom_elements_gridview->getCheckboxColumn(),
        ],

        'toolbar' =>  $custom_elements_gridview->getToolbar(),

        'panel' => $custom_elements_gridview->getPanel(),

        'toggleDataOptions' => $custom_elements_gridview->getTogleDataOptions(),
    ]); ?>
</div>

<?php
$url = Url::to([$controllerId . 'multiple_delete'], GlobalFunctions::URLTYPE);
$js = Footer_Bulk_Delete::getFooterBulkDelete($url);
$this->registerJs($js, View::POS_READY);
?>