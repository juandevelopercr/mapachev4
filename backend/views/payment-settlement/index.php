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
use common\models\User;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\InvoiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/' . $this->context->uniqueId . '/';
$this->title = Yii::t('backend', 'Liquidación de pagos');
$this->params['breadcrumbs'][] = $this->title;

$pdf_button = '';
?>

<?php
$pdf_button = Html::a('<i class="glyphicon glyphicon-print"></i> ' . Yii::t('backend', 'Estado de Cuenta'), ['#'], ['class' => 'btn btn-default btn-estado-cuenta btn-flat margin', 'title' => Yii::t('backend', 'Estado de cuenta')]);

$resetfiltros = Html::hiddenInput("clear-state", "1") . ' ' . Html::hiddenInput("redirect-to", "");


$custom_buttons = [
    'abonar' => function ($url, $model) {
        $options = [
            'title' => Yii::t('backend', 'Abonar'),
            'class' => 'btn btn-xs btn-default btn-flat',
            'aria-label' => Yii::t('backend', 'Abonar'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',
        ];
        if ($model->status === UtilsConstants::ACCOUNT_RECEIVABLE_PENDING) {
            return Html::a('<i class="glyphicon glyphicon-th-list"></i>', ['/accounts-receivable/add-abono', 'id' => $model->id], $options);
        }
    },
];
$custom_elements_gridview = new Custom_Settings_Column_GridView($resetfiltros, $dataProvider, ['view', 'update', 'abonar'], $custom_buttons);

$panels = [
    'before' => '',
    'after' => "<table cellpadding=\"10\" cellspacing=\"10\" align=\"right\" style=\"margin-top:20px\">
                    <tr>
                        <td class='text-right'>
                            <strong class='text-danger'>Total</strong>
                        </td>
                        <td align=\"right\">
                            &nbsp;&nbsp;<strong><span id=\"total_amount\" class='text-danger'></span></strong>
                        </td>
                    </tr>        
                    <tr>
                        <td width=\"150px\" class='text-right'>
                            <strong>Efectivo</strong>
                        </td>
                        <td align=\"right\" width=\"120px\">
                            &nbsp;&nbsp;<span id=\"total_efectivo\"></span>
                        </td>
                    </tr>																																
                    <tr>
                        <td class='text-right'>
                            <strong>Cheques</strong>
                        </td>
                        <td align=\"right\">
                            &nbsp;&nbsp;<span id=\"total_cheque\"></span>
                        </td>
                    </tr>																						
                    <tr>
                        <td class='text-right'>
                            <strong>Depósitos</strong>
                        </td>
                        <td align=\"right\">
                            &nbsp;&nbsp;<span id=\"total_deposito\"></span>
                        </td>
                    </tr>																						
                    <tr>
                        <td class='text-right'>
                            <strong>Sinpe Movil</strong>
                        </td>
                        <td align=\"right\">
                            &nbsp;&nbsp;<span id=\"total_sinpe_movil\"></span>
                        </td>
                    </tr>																						
                    <tr>
                        <td class='text-right'>
                            <strong class='text-danger'>Pendiente CxC</strong>
                        </td>
                        <td align=\"right\">
                            &nbsp;&nbsp;<strong><span id=\"total_diferencia\" class='text-danger'></span></strong>
                        </td>
                    </tr>                      
                </table>",
];
$custom_elements_gridview->setPanel($panels);
?>

<div class="box-body" id="panel-grid">
    <?= GridView::widget([
        'id' => 'grid_invoice_pendientes',
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
                'attribute' => 'customer_id',
                'format' => 'raw',
                'headerOptions' => ['class' => 'custom_width'],
                'contentOptions' => ['class' => 'custom_width kv-align-left kv-align-middle'],
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
                'attribute' => 'sellers',
                'format' => 'html',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => User::getSelectMapAgents(true, true),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                    'options' => ['multiple' => false],
                ],
                'value' => function ($model) {
                    return SellerHasInvoice::getSellerStringByInvoice($model->id);
                },
                'filterInputOptions' => ['placeholder' => '------'],
                'hAlign' => 'center',
            ],

            [
                'attribute' => 'status',
                'format' => 'html',
                'contentOptions' => ['class' => 'kv-align-center kv-align-middle'],
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => UtilsConstants::getCuentaCobrarStatus(),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                    'options' => ['multiple' => false],
                ],
                'value' => function ($model) {
                    $color = '';
                    if (is_null($model->status))
                        $model->status = UtilsConstants::ACCOUNT_RECEIVABLE_PENDING;

                    $status = (int) $model->status;

                    if ($status === UtilsConstants::ACCOUNT_RECEIVABLE_PENDING) {
                        $color = 'red';
                    } else {
                        $color = 'green';
                    }

                    return " <small class=\"badge bg-" . $color . "\"></i> " . UtilsConstants::getCuentaCobrarStatus($status, true) . "</small>";
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
                    $pendiente = $total - $abonado;

                    return $pendiente;
                },
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM,
            ],

            //$custom_elements_gridview->getActionColumn(),

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
$js = Footer_Bulk_Delete::getFooterBulkDelete($url, 'grid_invoice_pendientes');
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
                    var selectedId = $("#grid_invoice_pendientes").yiiGridView("getSelectedRows");
                    if(selectedId.length == 0) {
                        bootbox.alert("Seleccione un elemento"); 
                    } else {
                        var url = "' . Url::to(['/accounts-receivable/estado-cuenta-pdf'], GlobalFunctions::URLTYPE) . '?ids="+selectedId;
                        window.open(url);
                    }
                });	


                function updateValores()
                {
                    var ids = getDataKey();
                    if(ids != "")
                    {
                        $.ajax({
                            type: \'POST\',			
                            dataType: "json",
                            url : "' . Url::to(['/payment-settlement/get-resume-payment'], GlobalFunctions::URLTYPE) . '",
                            data : {ids: ids},
                            success : function(json) {			
                                $("#total_amount").html(json.total);					
                                $("#total_efectivo").html(json.efectivo);					
                                $("#total_cheque").html(json.cheque);					
                                $("#total_deposito").html(json.transferencia);
                                $("#total_sinpe_movil").html(json.sinpemovil);
                                $("#total_diferencia").html(json.diferencia);		
                            },
                            error: function(XMLHttpRequest, textStatus, errorThrown) {
                                $.notify({
                                    "message": "Ha ocurrido un error. Inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema",
                                    "icon": "glyphicon glyphicon-remove text-danger-sign",
                                    "title": "Error <hr class=\"kv-alert-separator\">",					
                                    "showProgressbar": false,
                                    "url":"",						
                                    "target":"_blank"},{"type": "danger"}
                                );
                            }					
                        });
                    }    		
                }

                function getDataKey(){
                    var ids=[];
                    //var t01=$("#pleinfoTab tr").length;
                    //console.log("t01:"+t01);
                    $(".kv-grid-table tbody").find("tr").each(function(i){
                        var obj=$(this).attr("data-key");
                        //console.log("obj:"+obj);
                        if (obj != "undefined")                        
                            ids.push(obj);     
                    }); 
                    return ids;                   
                }   

                updateValores();
           }
        
    init_click_handlers(); //first run
    $("#grid_invoice_pendientes-pjax").on("pjax:success", function() {
      init_click_handlers(); //reactivate links in grid after pjax update
    });
});
');

?>