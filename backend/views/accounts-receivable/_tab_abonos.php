<?php

use yii\helpers\Html;
use common\widgets\GridView;
use mdm\admin\components\Helper;
use yii\web\View;
use yii\helpers\Url;
use backend\components\Footer_Bulk_Delete;
use backend\components\Custom_Settings_Column_GridView;
use common\models\GlobalFunctions;
use backend\models\business\Customer;
use backend\models\business\InvoiceAbonos;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\RouteTransport;
use backend\models\nomenclators\UtilsConstants;
use backend\models\business\SellerHasInvoice;
use backend\models\business\CollectorHasInvoice;
use backend\models\nomenclators\PaymentMethod;
use common\models\User;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\InvoiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$pdf_button = '';
?>

<?php
$pdf_button = Html::a('<i class="glyphicon glyphicon-print"></i> ' . Yii::t('backend', 'Estado de Cuenta'), ['#'], ['class' => 'btn btn-default btn-estado-cuenta btn-flat margin', 'title' => Yii::t('backend', 'Estado de cuenta')]);


$custom_buttons = [
    'abonar' => function($url, $model) {
        $options = [
            'title' => Yii::t('backend', 'Abonar'),
            'class' => 'btn btn-xs btn-default btn-flat',
            'aria-label' => Yii::t('backend', 'Abonar'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',
        ];
        if ($model->status_cuenta_cobrar === UtilsConstants::ACCOUNT_RECEIVABLE_PENDING)
        {
            return Html::a('<i class="glyphicon glyphicon-th-list"></i>', ['/accounts-receivable/add-abono', 'id' => $model->id], $options);
        }
    },        
];
$custom_elements_gridview = new Custom_Settings_Column_GridView('', $dataProvider, ['view', 'abonar']);

$panels = [
    'before' => '',
    'after' => ''
];
$custom_elements_gridview->setPanel($panels);
?>

<div class="box-body" id="panel-grid">
    <?= GridView::widget([
        'id' => 'grid_invoice_abonos',
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
        'exportConfig' => [
            GridView::EXCEL => [
                'filename' => Yii::t("backend", "Reporte de estado de cuentas (" . GlobalFunctions::formatDateToShowInSistem(date('Y-m-d')) . ")"),
            ],
            GridView::PDF => [
                'filename' => Yii::t("backend", "Reporte de estado de cuentas (" . GlobalFunctions::formatDateToShowInSistem(date('Y-m-d')) . ")"),
                'config' => [
                    'methods' => [
                        'SetTitle' => 'Corbe Gourmet report',
                        'SetSubject' => 'Corbe Gourmet report',
                        'SetHeader' => ['Fecha: ' . GlobalFunctions::formatDateToShowInSistem(date('Y-m-d'))],
                        'SetFooter' => [Yii::t("backend", "Generado por: ") . User::getFullNameByActiveUser() . '|' . Yii::t("backend", "Página") . ' {PAGENO}|'],
                        'SetAuthor' => User::getFullNameByActiveUser(),
                        'SetCreator' => User::getFullNameByActiveUser(),
                        'SetKeywords' => 'Corbe Gourmet, reporte',
                    ],
                    'contentBefore' => Yii::t("backend", "Corbe Gourmet: Reporte de estado de cuentas"),
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
                'attribute' => 'invoice_type',
                'format' => 'html',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => UtilsConstants::getPreInvoiceSelectType(),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                    'options' => ['multiple' => false],
                ],
                'value' => function ($data) {
                    return UtilsConstants::getPreInvoiceSelectType($data->invoice_type);
                },
                'filterInputOptions' => ['placeholder' => '------'],
                'hAlign' => 'center',
                'pageSummary' => 'Total',
            ],

            [
                'attribute' => 'consecutive',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) {
                    return $data->consecutive;
                }
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
                            'format' => 'd-m-Y'
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
                'attribute' => 'credit_days_id',
                'format' => 'raw',
                'headerOptions' => ['class' => 'custom_width'],
                'contentOptions' => ['class' => 'kv-align-center kv-align-middle'],
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => CreditDays::getSelectMap(true, NULL),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                    'options' => ['multiple' => false],
                ],
                'value' => function ($model) {                    
                    //return $model->creditDays->name;
                    return 8;
                },
                'filterInputOptions' => ['placeholder' => '------'],
                'hAlign' => 'center',
            ],            
            [
                'attribute' => 'color',
                'label' => 'Días',
                'format' => 'html',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'], // <-- right here						
                'filter' => '<span class="badge" style="background-color: #dd4b39 !important;">N</span> Vencida<br />
                               <span class="badge" style="background-color: #f39c12 !important;">7</span> Para Vencer<br />
                               <span class="badge" style="background-color: #00a65a !important;">N</span> Para Vencer',
                'value' => function ($model, $key, $index, $widget) {
                    $mostrar = true;                    
                    if ($model->status_cuenta_cobrar == UtilsConstants::ACCOUNT_RECEIVABLE_CANCELLED)
                        $mostrar = false;
                    else
                        if ($model->dias_vencidos == 0) {
                        $color = 'green';
                        $dias = 0;
                        $texto = 'Para Vencer';
                    } else
                        if ($model->dias_vencidos > 0) {
                        $color = 'red';
                        $dias = abs($model->dias_vencidos);
                        $texto = 'Vencida';
                    } else
                        if (abs($model->dias_vencidos) <= 7) {
                        $color = 'yellow';
                        $dias = abs($model->dias_vencidos);
                        $texto = 'Para Vencer';
                    } else {
                        $color = 'green';
                        $dias = abs($model->dias_vencidos);
                        $texto = 'Para Vencer';
                    }

                    if ($mostrar)
                        return "<span data-toggle=\"" . $texto . "\" title=\"" . $texto . "\" class=\"badge bg-" . $color . "\">" . $dias . "</span><code>" . $texto . "</code>";
                    else
                        return '';
                },
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'format' => 'raw',
                'width' => '150px',
                'noWrap' => true
            ],
            [
                'attribute' => 'customer_id',
                'format' => 'raw',
                'headerOptions' => ['class' => 'custom_width'],
                'contentOptions' => ['class' => 'custom_width'],
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => Customer::getSelectMap(true, NULL),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                    'options' => ['multiple' => false],
                ],
                'value' => function ($model) {
                    $temp_name = (isset($model->customer->commercial_name) && !empty($model->customer->commercial_name)) ? $model->customer->name . ' - ' . $model->customer->commercial_name : $model->customer->name;
                    return $temp_name;
                },
                'filterInputOptions' => ['placeholder' => '------'],
                'hAlign' => 'center',
            ],
            [
                'attribute'=>'commercial_name',
                'headerOptions' => ['class'=>'custom_width'],
                'contentOptions' => ['class'=>'custom_width'],
                'hAlign'=>'center',
                'value' => function($data){
                    return $data->customer->commercial_name;
                },
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


            /*
            [
                'attribute'=>'payment_method_id',
                'format' => 'html',
                'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                'filterType'=>GridView::FILTER_SELECT2,
                'filter'=> PaymentMethod::getSelectMap(),
                'filterWidgetOptions' => [
                    'pluginOptions'=>['allowClear'=>true],
                    'options'=>['multiple'=>false],
                ],
                'value'=> function($model){
                    return $model->paymentMethod->name;
                },
                'filterInputOptions'=>['placeholder'=> '------'],
                'hAlign'=>'center',
            ], 
            */            

            [
                'attribute'=>'sellers',
                'format' => 'html',
                'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                'filterType'=>GridView::FILTER_SELECT2,
                'filter'=> User::getSelectMapAgents(true,true),
                'filterWidgetOptions' => [
                    'pluginOptions'=>['allowClear'=>true],
                    'options'=>['multiple'=>false],
                ],
                'value'=> function($model){
                    return SellerHasInvoice::getSellerStringByInvoice($model->id);
                },
                'filterInputOptions'=>['placeholder'=> '------'],
                'hAlign'=>'center',
            ],                

            [
                'attribute'=>'collectors',
                'format' => 'html',
                'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                'filterType'=>GridView::FILTER_SELECT2,
                'filter'=> User::getSelectMapAgents(true,true),
                'filterWidgetOptions' => [
                    'pluginOptions'=>['allowClear'=>true],
                    'options'=>['multiple'=>false],
                ],
                'value'=> function($model){
                    return CollectorHasInvoice::getCollectorStringByInvoice($model->id);
                },
                'filterInputOptions'=>['placeholder'=> '------'],
                'hAlign'=>'center',
            ],               

            [
                'attribute'=>'route_transport_id',
                'format' => 'html',
                'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                'filterType'=>GridView::FILTER_SELECT2,
                'filter'=> RouteTransport::getSelectMap(),
                'filterWidgetOptions' => [
                    'pluginOptions'=>['allowClear'=>true],
                    'options'=>['multiple'=>false],
                ],
                'value'=> function($model){
                    return (isset($model->route_transport_id) && !empty($model->route_transport_id))? $model->routeTransport->code.' - '.$model->routeTransport->name : '';
                },
                'filterInputOptions'=>['placeholder'=> '------'],
                'hAlign'=>'center',
            ],

            [
                'attribute' => 'status_cuenta_cobrar',
                'format' => 'html',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => UtilsConstants::getCuentaCobrarStatus(),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                    'options' => ['multiple' => false],
                ],
                'value' => function ($model) {
                    $color = '';
                    if (is_null($model->status))
                        $model->status = UtilsConstants::INVOICE_STATUS_PENDING;

                    $status = (int) $model->status;

                    if ($status === UtilsConstants::INVOICE_STATUS_PENDING) {
                        $color = 'red';
                    } 
                    else {
                        $color = 'green';
                    }

                    return " <small class=\"badge bg-" . $color . "\"></i> " . UtilsConstants::getCuentaCobrarStatus($status, true) . "</small>";
                },
                'filterInputOptions' => ['placeholder' => '------'],
                'hAlign' => 'center',
            ],

            
            [
                'label' => 'Cantidad',
                'contentOptions' => ['class' => 'kv-align-center kv-align-middle'],
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) {
                    return $data->getTotalItem();
                },
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM,
            ],

            [
                'attribute'=>'total_tax',
                'contentOptions'=>['class'=>'kv-align-right kv-align-middle'], // <-- right here
                'format'=>['decimal', 2],
                'hAlign'=>'right',
                'value'=>function ($model, $key, $index, $widget) {
                    return $model->total_tax;
                },
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM,
            ],

            [
                'attribute'=>'total_comprobante',
                'contentOptions'=>['class'=>'kv-align-right kv-align-middle'], // <-- right here
                'format'=>['decimal', 2],
                'hAlign'=>'right',
                'value'=>function ($model, $key, $index, $widget) {
                    return $model->total_comprobante;
                },
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM,
            ],

            [
                'label' => 'Abonado',
                'contentOptions' => ['class' => 'kv-align-right kv-align-middle'], // <-- right here
                'filter' => '',
                'format' => ['decimal', 2],
                'hAlign' => 'right',
                'value' => function ($model, $key, $index, $widget) {
                    $data = InvoiceAbonos::getAbonosByInvoiceID($model->id);
                    return $data;
                },
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM,
            ],  

            [
                'label' => 'Pendiente CxC',
                'contentOptions' => ['class' => 'kv-align-right kv-align-middle'], // <-- right here
                'filter' => '',
                'format' => ['decimal', 2],
                'hAlign' => 'right',
                'value' => function ($model, $key, $index, $widget) {
                    $total = $model->total_comprobante;
                    $abonado = InvoiceAbonos::getAbonosByInvoiceID($model->id);
                    //$pendiente = $total - $abonado;

                    // Redondear los valores a 2 decimales
                    $total = round($total, 2);
                    $abonado = round($abonado, 2);

                    // Realizar la resta redondeada
                    $pendiente = round($total - $abonado, 2);

                    // Comparar con cero considerando un margen de error pequeño
                    if (abs($pendiente) < 0.01) {
                        $pendiente = 0;
                    } 
                    return $pendiente;
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
$url = ''; //Url::to([$controllerId . 'multiple_delete'], GlobalFunctions::URLTYPE);
$js = Footer_Bulk_Delete::getFooterBulkDelete($url, 'grid_invoice_abonos');
$this->registerJs($js, View::POS_READY);
?>

<?php
// Register action buttons js
$this->registerJs('
    $(document).ready(function()
    {
        function init_click_handlers(){                
                $("a.btn-estado-cuenta").click(function(e) {
                    e.preventDefault();        
                    var selectedId = $("#grid_invoice_abonos").yiiGridView("getSelectedRows");
                    if(selectedId.length == 0) {
                        bootbox.alert("Seleccione un elemento"); 
                    } else {
                        var url = "' . Url::to(['/accounts-receivable/estado-cuenta-pdf'], GlobalFunctions::URLTYPE) . '?ids="+selectedId;
                        window.open(url);
                    }
                });	
        }
        
    init_click_handlers(); //first run
    $("#grid_invoice_abonos-pjax").on("pjax:success", function() {
      init_click_handlers(); //reactivate links in grid after pjax update
    });
});
');

?>