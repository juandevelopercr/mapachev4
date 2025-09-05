<?php

use yii\helpers\Html;
use common\widgets\GridView;
//use kartik\grid\GridView;
use mdm\admin\components\Helper;
use yii\web\View;
use yii\helpers\Url;
use backend\components\Footer_Bulk_Delete;
use backend\components\Custom_Settings_Column_GridView;
use common\models\GlobalFunctions;
use backend\models\business\Customer;
use backend\models\business\SellerHasInvoice;
use backend\models\business\CollectorHasInvoice;
use backend\models\business\Supplier;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use backend\models\nomenclators\RouteTransport;
use common\models\User;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\InvoiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/' . $this->context->uniqueId . '/';
$this->title = Yii::t('backend', 'Gastos Manuales');
$this->params['breadcrumbs'][] = $this->title;

$create_button = '';
?>

<?php
if (Helper::checkRoute($controllerId . 'create')) {
    $create_button = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('backend', 'Crear'), ['create'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Crear')]);
};

$custom_buttons = [
    'update' => function ($url, $model) {
        $options = [
            'title' => Yii::t('backend', 'Actualizar'),
            'class' => 'btn btn-xs btn-default btn-flat',
            'aria-label' => Yii::t('backend', 'Actualizar'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',
        ];
        if ($model->status === UtilsConstants::INVOICE_STATUS_PENDING) {
            return Html::a('<i class="glyphicon glyphicon-pencil"></i>', ['/manual-invoice/update', 'id' => $model->id], $options);
        }
    },
    'pdf' => function ($url, $model) {
        $options = [
            'title' => Yii::t('backend', 'FE-PDF'),
            'class' => 'btn btn-xs btn-default btn-flat',
            'aria-label' => Yii::t('backend', 'FE-PDF'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',
            'target' => '_blank'
        ];
        return Html::a('<i class="fa fa-file-pdf-o"></i>', ['/invoice/viewpdf', 'id' => $model->id], $options);
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
        if ($model->status === UtilsConstants::INVOICE_STATUS_PENDING) {
            return Html::a('<i class="glyphicon glyphicon-trash"></i>', ['/invoice/delete', 'id' => $model->id], $options);
        }
    },
];
$custom_elements_gridview = new Custom_Settings_Column_GridView($create_button, $dataProvider, ['update', 'delete'], $custom_buttons);

$panels = [
    'before' => '',
    'after' => '',
];
$custom_elements_gridview->setPanel($panels);
?>

<div class="box-body" id="panel-grid">
    <?= GridView::widget([
        'id' => 'grid_manual_invoice',
        'dataProvider' => $dataProvider,
        'pjax' => true,
        'pjaxSettings' => [
            'neverTimeout' => true,
            'options' => [
                'enablePushState' => false,
                'scrollTo' => false,
            ],
        ],
        'autoXlFormat' => true,
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
        'export' => [
            //'header'=> "Allergan report",
            'options' => ['class' => 'btn btn-default btn-flat margin dropdown-toggle'],

        ],
        'exportConfig' => [

            GridView::EXCEL => [
                'filename' => Yii::t("backend", "Reporte de gastos manuales (" . GlobalFunctions::formatDateToShowInSistem(date('Y-m-d')) . ")"),
            ],
            GridView::PDF => [
                'filename' => Yii::t("backend", "Reporte de gastos manuales (" . GlobalFunctions::formatDateToShowInSistem(date('Y-m-d')) . ")"),
                'config' => [
                    'methods' => [
                        'SetTitle' => 'Peport',
                        'SetSubject' => 'Report',
                        'SetHeader' => ['Fecha: ' . GlobalFunctions::formatDateToShowInSistem(date('Y-m-d'))],
                        'SetFooter' => [Yii::t("backend", "Generado por: ") . User::getFullNameByActiveUser() . '|' . Yii::t("backend", "Página") . ' {PAGENO}|'],
                        'SetAuthor' => User::getFullNameByActiveUser(),
                        'SetCreator' => User::getFullNameByActiveUser(),
                        'SetKeywords' => 'Corbe Gourmet, reporte',
                    ],
                    'contentBefore' => Yii::t("backend", "Reporte de gastos manuales"),
                    'contentAfter' => ""
                ]
            ],
        ],
        'persistResize' => true,
        'showPageSummary' => true,
        'filterModel' => $searchModel,
        'as filterBehavior' => \thrieu\grid\FilterStateBehavior::className(),
        'columns' => [

            $custom_elements_gridview->getSerialColumn(),

            [
                'attribute' => 'consecutive',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) {
                    return Html::a($data->consecutive, ['/invoice/update', 'id' => $data->id]);
                }
            ],
            [
                'attribute'=>'supplier_id',
                'format' => 'html',
                'headerOptions' => ['class'=>'custom_width'],
                'contentOptions' => ['class'=>'custom_width'],
                'filterType'=>GridView::FILTER_SELECT2,
                'filter'=> Supplier::getSelectMap(),
                'filterWidgetOptions' => [
                    'pluginOptions'=>['allowClear'=>true],
                    'options'=>['multiple'=>false],
                ],
                'value'=> function($model){
                    return $model->supplier->code.' - '.$model->supplier->name;
                },
                'filterInputOptions'=>['placeholder'=> '------'],
                'hAlign'=>'center',
            ],
            [
                'attribute' => 'emission_date',
                'value' => function ($data) {
                    return GlobalFunctions::formatDateToShowInSystem($data->emission_date);
                },
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'hAlign' => 'center',
                'filterType' => GridView::FILTER_DATE_RANGE,
                'filterWidgetOptions' => ([
                    'model' => $searchModel,
                    'attribute' => 'emission_date',
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
                'attribute' => 'currency_id',
                'format' => 'html',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => Currency::getSelectMap(),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                    'options' => ['multiple' => false],
                ],
                'value' => 'currency.symbol',
                'filterInputOptions' => ['placeholder' => '------'],
                'hAlign' => 'center',
            ],
            [
                'attribute' => 'status',
                'format' => 'html',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => UtilsConstants::getInvoiceStatusSelectType(),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                    'options' => ['multiple' => false],
                ],
                'value' => function ($data) {
                    return UtilsConstants::getInvoiceStatusSelectType($data->status);
                },
                'filterInputOptions' => ['placeholder' => '------'],
                'hAlign' => 'center',
            ],

            [
                'attribute' => 'total_comprobante',
                'contentOptions' => ['class' => 'kv-align-right kv-align-middle'], // <-- right here
                'format' => ['decimal', 2],
                'hAlign' => 'right',
                'value' => function ($model, $key, $index, $widget) {
                    return $model->total_comprobante;
                },
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM,
            ],

            $custom_elements_gridview->getActionColumn(),            

            $custom_elements_gridview->getCheckboxColumn(),

        ],

        'toolbar' =>  $custom_elements_gridview->getToolbar(),

        'panel' => $custom_elements_gridview->getPanel(),

        'toggleDataOptions' => $custom_elements_gridview->getTogleDataOptions(),
    ]); ?>
</div>

<div id="panel-form-enviar"></div>

<?php
$url = Url::to([$controllerId . 'multiple_delete'], GlobalFunctions::URLTYPE);
$js = Footer_Bulk_Delete::getFooterBulkDelete($url, 'grid_manual_invoice');
$this->registerJs($js, View::POS_READY);
?>

<?php
// Register action buttons js
$this->registerJs('
    $(document).ready(function()
    {
        function init_click_handlers(){
                
        }
        
    init_click_handlers(); //first run
    $("#grid_manual_invoice-pjax").on("pjax:success", function() {
      init_click_handlers(); //reactivate links in grid after pjax update
    });
});
');

?>