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
use backend\models\business\AccountsPayableAbonos;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\RouteTransport;
use backend\models\nomenclators\UtilsConstants;
use backend\models\business\SellerHasInvoice;
use backend\models\business\CollectorHasInvoice;
use backend\models\business\Documents;
use backend\models\settings\Setting;
use common\models\User;
use phpDocumentor\Reflection\DocBlock\Tags\Uses;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\InvoiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/' . $this->context->uniqueId . '/';
//$this->title = Yii::t('backend', 'Cuentas por cobrar');
//$this->params['breadcrumbs'][] = $this->title;

$pdf_button = '';
$create_button = '';
$setting = Setting::findOne(1);
?>

<?php
if (Helper::checkRoute($controllerId . 'create')) {
    $create_button = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('backend', 'Crear'), ['create'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Crear') . ' ' . Yii::t('backend', 'Cuentas por Pagar')]);
};

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
            return Html::a('<i class="glyphicon glyphicon-th-list"></i>', ['/accounts-payable/add-abono', 'id' => $model->id], $options);
        }
    },
];
$custom_elements_gridview = new Custom_Settings_Column_GridView($create_button . $resetfiltros, $dataProvider, ['view', 'abonar'], $custom_buttons);

$panels = [
    'before' => '',
    'after' => ''
];
$custom_elements_gridview->setPanel($panels);
?>

<div class="box-body" id="panel-grid-pendientes">
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
                'attribute' => 'key',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) {
                    if (strlen($data->key) < 50)
                        $consecutivo = $data->key;
                    else
                    {    
                        $consecutivo = substr($data->key, 21);
                        $consecutivo = substr($consecutivo, 0, 20);
                    }
                    return $consecutivo;
                }
            ],
            [
                'attribute' => 'transmitter',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) use ($setting) {
                    $proveedor = '';
                    if (strlen($data->key) < 50){
                        $proveedor = $data->proveedor;
                    }
                    else
                    {
                        $documento = Documents::find()->where(['key' => $data->key])->one();
                        if (!is_null($documento)) {
                            $proveedor = $documento->transmitter;
                        }
                    }
                    return $proveedor;
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
                'attribute' => 'currency',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) {
                    return $data->currency;
                }
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
                    if ($model->status == UtilsConstants::ACCOUNT_PAYABLE_CANCELLED)
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
                'label' => 'Documentos',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle col-documento'],
                'hAlign' => 'center',
                'format' => 'raw',
                'value' => function ($data) {
                    $url = urldecode(Url::toRoute([
                        'update',
                        'id' => $data->id,
                    ]));
                    $color = 'green';
                    $color1 = 'blue';
                    $texto = $data->key;
                    $btnpdf = '';
                    $btnxml = '';
                    $btnxml_hacienda = '';

                    $documento = Documents::find()->where(['key' => $data->key])->one();
                    if (!is_null($documento)) {

                        $url_xml = $documento->getFileUrlXML();
                        $url_pdf = $documento->getFileUrlPDF();
                        $url_xml_hacienda = Yii::getAlias('/backend/web/documents/' . $documento->url_ahc);
                        $url_xml_hacienda_verificar = Yii::getAlias('@backend/web/documents/' . $documento->url_ahc);
                        $tipo = '';
                        switch ($documento->document_type) {
                            case '01':
                                $tipo = 'FE';
                                break;
                            case '02':
                                $tipo = 'ND';
                                break;
                            case '03':
                                $tipo = 'NC';
                                break;
                            case '04':
                                $tipo = 'TE';
                                break;
                            case '05':
                                $tipo = 'MR';
                                break;
                            case '06':
                                $tipo = 'MR';
                                break;
                            case '07':
                                $tipo = 'MR';
                                break;
                            case '08':
                                $tipo = 'FEC';
                                break;
                            case '09':
                                $tipo = 'FEE';
                        }
                        $btnpdf = '';
                        $btnxml = '';

                        if (!is_null($url_pdf) && !empty($url_pdf))
                            $btnpdf = "<a href=\"" . $url_pdf . "\" title=\"" . $documento->key . "\" data-method=\"post\" data-pjax=\"0\" target=\"_blank\" class=\"badge bg-" . $color1 . "\"><i class=\"fa fa-fw fa-download\"></i> " . $tipo . "-PDF</a>";

                        if (!is_null($url_xml) && !empty($url_xml))
                            $btnxml = "<a href=\"" . $url_xml . "\" title=\"" . $documento->key . "\" data-method=\"post\" data-pjax=\"0\" target=\"_blank\" class=\"badge bg-" . $color1 . "\"><i class=\"fa fa-fw fa-download\"></i> " . $tipo . "-XML</a>";

                        $btnxml_hacienda = '';
                        if (!is_null($documento->url_ahc) && !empty($documento->url_ahc)) {
                            $url_xml_hacienda_verificar = Yii::getAlias('@backend/web/uploads/xmlh/' . $documento->url_ahc);
                            $url = Yii::getAlias('/backend/web/uploads/xmlh/' . $documento->url_ahc);
                            if (file_exists($url_xml_hacienda_verificar)) {
                                $btnxml_hacienda = "<a href=\"" . $url . "\" title=\"" . $documento->key . "\" data-method=\"post\" data-pjax=\"0\" target=\"_blank\" class=\"badge bg-" . $color1 . "\"><i class=\"fa fa-fw fa-download\"></i> " . $tipo . "-XML-H</a>";
                            }
                        }
                    }

                    return $btnpdf . ' ' . $btnxml . ' ' . $btnxml_hacienda;
                }
            ],

            [
                'attribute' => 'status',
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
                        $model->status = UtilsConstants::ACCOUNT_PAYABLE_PENDING;

                    $status = (int) $model->status;

                    if ($status === UtilsConstants::ACCOUNT_PAYABLE_PENDING) {
                        $color = 'red';
                    } else {
                        $color = 'green';
                    }

                    return " <small class=\"badge bg-" . $color . "\"></i> " . UtilsConstants::getCuentaCobrarStatus($status, true) . "</small>";
                },
                'filterInputOptions' => ['placeholder' => '------'],
                'hAlign' => 'center',
            ],

            $custom_elements_gridview->getActionColumn(),
            [
                'attribute' => 'total_invoice',
                'contentOptions' => ['class' => 'kv-align-right kv-align-middle'], // <-- right here
                'format' => ['decimal', 2],
                'hAlign' => 'right',
                'value' => function ($model, $key, $index, $widget) {
                    return $model->total_invoice;
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
                    $data = AccountsPayableAbonos::getAbonosByInvoiceID($model->id);
                    return $data;
                },
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM,
            ],

            [
                'label' => 'Pendiente CxP',
                'contentOptions' => ['class' => 'kv-align-right kv-align-middle'], // <-- right here
                'filter' => '',
                'format' => ['decimal', 2],
                'hAlign' => 'right',
                'value' => function ($model, $key, $index, $widget) {
                    $total = $model->total_invoice;
                    $abonado = AccountsPayableAbonos::getAbonosByInvoiceID($model->id);
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

            $custom_elements_gridview->getCheckboxColumn(),

        ],

        'toolbar' =>  $custom_elements_gridview->getToolbar(),

        'panel' => $custom_elements_gridview->getPanel(),

        'toggleDataOptions' => $custom_elements_gridview->getTogleDataOptions(),
    ]); ?>
</div>

<div id="panel-form-enviar"></div>