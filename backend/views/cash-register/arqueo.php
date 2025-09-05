<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use mdm\admin\components\Helper;
use yii\web\View;
use yii\helpers\Url;
use backend\components\Footer_Bulk_Delete;
use backend\components\Custom_Settings_Column_GridView;
use backend\models\business\MovementCashRegister;
use common\models\GlobalFunctions;
use yii\helpers\BaseStringHelper;
use backend\models\nomenclators\BranchOffice;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\CashRegisteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/' . $this->context->uniqueId . '/';
$this->title = Yii::t('backend', 'Arqueos de Caja: ').$box->numero.'-'.$box->name;
$this->params['breadcrumbs'][] = ['label' => 'Cajas', 'url' => ['/boxes/index']];
$this->params['breadcrumbs'][] = $this->title;

$create_button = '';
$add_efectivo = '';
$retirar_efectivo = '';
?>

<?php
if (Helper::checkRoute($controllerId . 'create')) {
    $create_button = Html::a('<i class="fa fa-money"></i> ' . Yii::t('backend', 'Abrir Caja'), ['open-box', 'box_id'=>$box->id], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Abrir Caja')]);
}

//$add_efectivo = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('backend', 'Adicionar efectivo'), ['add-cash', 'box_id'=>$box->id], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Adicionar efectivo')]);
//$retirar_efectivo = Html::a('<i class="fa fa-minus"></i> ' . Yii::t('backend', 'Extraer efectivo'), ['extract-cash', 'box_id'=>$box->id], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Extraer efectivo')]);

$custom_buttons = [
    'adicionar' => function ($url, $model) {
        $options = [
            'title' => Yii::t('backend', 'Adicionar efectivo'),
            'class' => 'btn btn-xs btn-flat btn-success',
            'aria-label' => Yii::t('backend', 'Adicionar efectivo'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',                  
        ];
        if ($model->status == 1)
            return Html::a('<i class="glyphicon glyphicon-plus"></i>', ['/cash-register/adicionar', 'cash_register_id'=>$model->id, 'box_id' => $model->box_id], $options);
    },    
    'retirar' => function ($url, $model) {
        $options = [
            'title' => Yii::t('backend', 'Retirar efectivo'),
            'class' => 'btn btn-xs btn-flat btn-warning',
            'aria-label' => Yii::t('backend', 'Retirar efectivo'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',                 
        ];
        if ($model->status == 1)
            return Html::a('<i class="glyphicon glyphicon-minus"></i>', ['/cash-register/retirar', 'cash_register_id'=>$model->id, 'box_id' => $model->box_id], $options);
    },      
    'cerrar' => function ($url, $model) {
        $options = [
            'title' => Yii::t('backend', 'Cerrar Caja'),
            'class' => 'btn btn-xs btn-flat btn-danger',
            'aria-label' => Yii::t('backend', 'Cerrar Caja'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',                   
        ];
        if ($model->status == 1)
            return Html::a('<i class="glyphicon glyphicon-remove-sign"></i>', ['/cash-register/cerrar', 'cash_register_id' => $model->id], $options);
    },
    'print-openbox' => function ($url, $model) {
        $options = [
            'title' => Yii::t('backend', 'Reporte de apertura de caja'),
            'class' => 'btn btn-xs btn-default btn-flat',
            'aria-label' => Yii::t('backend', 'Reporte de apertura de caja'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',
            'target'=> '_blank',
        ];
        //$movimiento = MovementCashRegister::find()->where(['cash_register_id'=>$model->id])->one();
        return Html::a('<i class="glyphicon glyphicon-print"></i>', ['/cash-register/view-cash-opening-report', 'cash_register_id' => $model->id], $options);
    },
    'print-closebox' => function ($url, $model) {
        $options = [
            'title' => Yii::t('backend', 'Reporte de cierre de caja'),
            'class' => 'btn btn-xs btn-default btn-flat',
            'aria-label' => Yii::t('backend', 'Reporte de cierre de caja'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',
            'target'=> '_blank',
        ];
        //$movimiento = MovementCashRegister::find()->where(['cash_register_id'=>$model->id])->one();
        if ($model->status == 0)
            return Html::a('<i class="glyphicon glyphicon-print"></i>', ['/cash-register/view-cash-close-report', 'cash_register_id' => $model->id], $options);
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
        if ($model->status == 1)
            return Html::a('<i class="glyphicon glyphicon-pencil"></i>', ['/cash-register/update-arqueo', 'id' => $model->id], $options);
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
        if ($model->status == 1)
            return Html::a('<i class="glyphicon glyphicon-trash"></i>', ['/cash-register/delete', 'id' => $model->id], $options);
    },
];

$custom_elements_gridview = new Custom_Settings_Column_GridView($create_button, $dataProvider, ['adicionar','retirar','cerrar', 'print-openbox', 'print-closebox', 'update', 'delete'], $custom_buttons);
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
        'columns' => [

            $custom_elements_gridview->getSerialColumn(),
            [
                'attribute'=>'opening_date',
                'value' => function($data){
                    return GlobalFunctions::formatDateToShowInSystem($data->opening_date). ' '.date('h:i:s A', strtotime($data->opening_time));
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
                'attribute'=>'closing_date',
                'value' => function($data){
                    if ($data->status == 0)
                        return GlobalFunctions::formatDateToShowInSystem($data->closing_date). ' '.date('h:i:s A', strtotime($data->closing_time));
                    else
                        return '';    
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
            [
                'attribute' => 'status',
                'contentOptions' => ['class' => 'kv-align-center kv-align-middle'],
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) {
                    if ($data->status == 1){
                        $estado = 'Abierta';
                        $color = 'green';
                    }
                    else{
                        $estado = 'Cerrada';                        
                        $color = 'red';
                    }

                    return " <small class=\"badge bg-".$color."\"></i> ". $estado."</small>";
                    return $estado;    
                }
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