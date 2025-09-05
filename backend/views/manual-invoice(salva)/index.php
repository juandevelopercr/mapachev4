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
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use backend\models\nomenclators\RouteTransport;
use common\models\User;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\InvoiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = Yii::t('backend', 'Facturas y tiquetes');
$this->params['breadcrumbs'][] = $this->title;

$create_button='';
?>

<?php 
	if (Helper::checkRoute($controllerId . 'create')) {
        $create_button = Html::a('<i class="fa fa-file-pdf-o"></i> '.Yii::t('backend', 'Preparación'), '#', ['class' => 'btn btn-default btn-flat margin btn-pdf-preparation', 'title' => Yii::t('backend', 'Reporte de preparación de mercancías')])
        .' '. Html::a('<i class="fa fa-plus"></i> '.Yii::t('backend', 'Crear'), ['create'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Crear').' '.Yii::t('backend', 'Factura')]);
	};

	$custom_buttons = [
        'update' => function($url, $model) {
            $options = [
                'title' => Yii::t('backend', 'Actualizar'),
                'class' => 'btn btn-xs btn-default btn-flat',
                'aria-label' => Yii::t('backend', 'Actualizar'),
                'data-method' => 'post',
                'data-pjax' => '0',
                'data-toggle' => 'tooltip',
            ];
            if ($model->status_hacienda === UtilsConstants::HACIENDA_STATUS_NOT_SENT)
            {
                return Html::a('<i class="glyphicon glyphicon-pencil"></i>', Url::to(['/invoice/update', 'id' => $model->id], GlobalFunctions::URLTYPE), $options);
            }
        },
        'corregir' => function($url, $model) {
            $options = [
                'title' => Yii::t('backend', 'Corregir documento'),
                'class' => 'btn btn-xs btn-default btn-flat',
                'aria-label' => Yii::t('backend', 'Corregir documento'),
                'data-method' => 'post',
                'data-pjax' => '0',
                'data-toggle' => 'tooltip',
                'data-confirm' => Yii::t('backend', '¿Seguro desea corregir este documento? Este proceso creará un duplicado del documento y anulará el documento actual'),

            ];
            if ($model->status_hacienda === UtilsConstants::HACIENDA_STATUS_REJECTED)
            {
                return Html::a('<i class="fa fa-remove text-danger"></i>', Url::to(['/invoice/correction', 'id' => $model->id], GlobalFunctions::URLTYPE), $options);
            }
        },
        'pdf_contingencia' => function($url, $model) {
            $options = [
                'title' => Yii::t('backend', 'PDF-Contingencia'),
                'class' => 'btn btn-xs btn-default btn-flat',
                'aria-label' => Yii::t('backend', 'PDF-Contingencia'),
                'data-method' => 'post',
                'data-pjax' => '0',
                'data-toggle' => 'tooltip',
                'target' => '_blank'
            ];
            return Html::a('<i class="fa fa-file-text-o"></i>', Url::to(['/invoice/viewpdfcontingencia', 'id' => $model->id], GlobalFunctions::URLTYPE), $options);
        },
        'pdf' => function($url, $model) {
            $options = [
                'title' => Yii::t('backend', 'FE-PDF'),
                'class' => 'btn btn-xs btn-default btn-flat',
                'aria-label' => Yii::t('backend', 'FE-PDF'),
                'data-method' => 'post',
                'data-pjax' => '0',
                'data-toggle' => 'tooltip',
                'target' => '_blank'
            ];
            return Html::a('<i class="fa fa-file-pdf-o"></i>', Url::to(['/invoice/viewpdf', 'id' => $model->id], GlobalFunctions::URLTYPE), $options);
        },
        'print' => function($url, $model) {
            $doc_type = (int) $model->invoice_type;
            if($doc_type === UtilsConstants::PRE_INVOICE_TYPE_INVOICE)
            {
                $options = [
                    'title' => Yii::t('backend', 'FE-IMP'),
                    'class' => 'btn btn-xs btn-default btn-flat',
                    'aria-label' => Yii::t('backend', 'FE-IMP'),
                    'data-method' => 'post',
                    'data-pjax' => '0',
                    'data-toggle' => 'tooltip',
                    'target' => '_blank'
                ];
                return Html::a('<i class="fa fa-print"></i>', Url::to(['/invoice/printpdf', 'id' => $model->id], GlobalFunctions::URLTYPE), $options);
            }

        },
        'ticket' => function($url, $model) {
            $options = [
                'title' => Yii::t('backend', 'TE-PDF'),
                'class' => 'btn btn-xs btn-default btn-flat',
                'aria-label' => Yii::t('backend', 'TE-PDF'),
                'data-method' => 'post',
                'data-pjax' => '0',
                'data-toggle' => 'tooltip',
                'target' => '_blank'
            ];
            return Html::a('<i class="fa fa-ticket"></i>', Url::to(['/invoice/viewticketpdf', 'id' => $model->id], GlobalFunctions::URLTYPE), $options);
        },
        'xml' => function($url, $model) {
            $options = [
                'title' => Yii::t('backend', 'FE-XML'),
                'class' => 'btn btn-xs btn-default btn-flat',
                'aria-label' => Yii::t('backend', 'FE-XML'),
                'data-method' => 'post',
                'data-pjax' => '0',
                'data-toggle' => 'tooltip',
                'target' => '_blank'
            ];
            return Html::a('<i class="fa fa-file-code-o"></i>', Url::to(['/invoice/viewxml', 'id' => $model->id], GlobalFunctions::URLTYPE), $options);
        },
        'xmlh' => function($url, $model) {
            $options = [
                'title' => Yii::t('backend', 'FE-MH-XML'),
                'class' => 'btn btn-xs btn-default btn-flat',
                'aria-label' => Yii::t('backend', 'FE-MH-XML'),
                'data-method' => 'post',
                'data-pjax' => '0',
                'data-toggle' => 'tooltip',
                'target' => '_blank'
            ];

            $url_xml_hacienda_verificar = Yii::getAlias('@backend/web/uploads/xmlh/FE-MH-'.$model->key.'.xml');
            $url = Yii::getAlias('/backend/web/uploads/xmlh/FE-MH-'.$model->key.'.xml');
            if (file_exists($url_xml_hacienda_verificar))
            {
                return Html::a('<i class="fa fa-file-text"></i>', $url, $options);
            }
        },
        'delete' => function($url, $model) {
            $options = [
                'title' => Yii::t('backend', 'Eliminar'),
                'class' => 'btn btn-xs btn-danger btn-flat',
                'aria-label' => Yii::t('backend', 'Eliminar'),
                'data-method' => 'post',
                'data-pjax' => '0',
                'data-toggle' => 'tooltip',
                'data-confirm' => Yii::t('backend', '¿Seguro desea eliminar este elemento?'),

            ];
            if ($model->status_hacienda === UtilsConstants::HACIENDA_STATUS_NOT_SENT)
            {
                return Html::a('<i class="glyphicon glyphicon-trash"></i>', Url::to(['/invoice/delete', 'id' => $model->id], GlobalFunctions::URLTYPE), $options);
            }
        },
    ];
	$custom_elements_gridview = new Custom_Settings_Column_GridView($create_button,$dataProvider,['view','update','corregir','pdf_contingencia','pdf','print','ticket','xml','xmlh','delete'],$custom_buttons);

$panels = [
    'before' => '<span style="margin-right: 5px;">'.
        '<span class="kv-grid-toolbar" style="float: right; margin: 10px;">
							<div class="btn-group">
							  <button class="btn btn-default dropdown-toggle" title="Notas" data-toggle="dropdown"><i class="glyphicon glyphicon-file"></i> Notas <span class="caret"></span></button>
							  <ul class="dropdown-menu dropdown-menu-right">
								<li title="Nota de Crédito">'.
        Html::a('<i class="glyphicon glyphicon-list-alt"></i> '.Yii::t('app', 'Crear Nota de Crédito'), ['#'], ['class' => 'btn btn-default btn-nota-credito'])
        .'
								</li>
								<li title="Nota de Débito">'.
        Html::a('<i class="glyphicon glyphicon-list-alt"></i> '.Yii::t('app', 'Crear Nota de Débito'),['#'], ['class' => 'btn btn-default btn-nota-debito'])
        .'
								</li>								
							  </ul>
							</div>					
					</span>'.
        '<span style="float: right; margin: 10px;">'.
        Html::a('<i class="glyphicon glyphicon-transfer"></i> Enviar a Hacienda',['#'], ['class' => 'btn btn-default btn-send-factura']).'</span>'.
        '<span style="margin-right: 5px;">'.
        '<span class="kv-grid-toolbar" style="float: right; margin: 10px;">
							<div class="btn-group">
							  <button class="btn btn-default dropdown-toggle" title="Impresión en bloques" data-toggle="dropdown"><i class="glyphicon glyphicon-file"></i> Impresión bloque <span class="caret"></span></button>
							  <ul class="dropdown-menu dropdown-menu-right">
								<li title="Imprimir tiquetes">'.
        Html::a('<i class="fa fa-ticket"></i> '.Yii::t('app', 'Tiquetes'), ['#'], ['class' => 'btn btn-default btn-imp-tickets'])
        .'
								</li>							
							  </ul>
							</div>					
					</span>'
    ,
    'after' => Html::a('<i class="fa fa-envelope"></i> Enviar Factura y XML',['#'], ['class' => 'btn btn-default btn-sendemail-factura']).'&nbsp;&nbsp;'.
        Html::a('<i class="glyphicon glyphicon-transfer"></i> Obtener Estado en Hacienda',['#','p_reset'=>true], ['class' => 'btn btn-default btn-get-estado-factura']),
];
	$custom_elements_gridview->setPanel($panels);
?>

    <div class="box-body" id="panel-grid">
        <?= GridView::widget([
            'id'=>'grid_invoice',
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
                'position'=>'absolute',
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
                    'filename' => Yii::t("backend", "Reporte de facturas electrónicas (" . GlobalFunctions::formatDateToShowInSistem(date('Y-m-d')) . ")"),
                ],
                GridView::PDF => [
                    'filename' => Yii::t("backend", "Reporte de facturas electrónicas (" . GlobalFunctions::formatDateToShowInSistem(date('Y-m-d')) . ")"),
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
                        'contentBefore' => Yii::t("backend", "Corbe Gourmet: Reporte de facturas electrónicas"),
                        'contentAfter' => ""
                    ]
                ],
            ],     
            'persistResize'=>true,
            'showPageSummary' => true,
            'filterModel' => $searchModel,
            'as filterBehavior' => \thrieu\grid\FilterStateBehavior::className(),	
            'columns' => [

				$custom_elements_gridview->getSerialColumn(),

                [
                    'attribute'=>'invoice_type',
                    'format' => 'html',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'filterType'=>GridView::FILTER_SELECT2,
                    'filter'=> UtilsConstants::getPreInvoiceSelectType(),
                    'filterWidgetOptions' => [
                        'pluginOptions'=>['allowClear'=>true],
                        'options'=>['multiple'=>false],
                    ],
                    'value'=> function($data){
                        return UtilsConstants::getPreInvoiceSelectType($data->invoice_type);
                    },
                    'filterInputOptions'=>['placeholder'=> '------'],
                    'hAlign'=>'center',
                ],

				[
					'attribute'=>'consecutive',
					'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
					'hAlign'=>'center',
					'format'=> 'html',
					'value' => function ($data) {
						return Html::a($data->consecutive, ['/invoice/update', 'id' => $data->id]);
					}
				],

                [
                    'attribute'=>'emission_date',
                    'value' => function($data){
                        return GlobalFunctions::formatDateToShowInSystem($data->emission_date);
                    },
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'hAlign'=>'center',
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
					'attribute'=>'customer_id',
                    'format' => 'raw',
                    'headerOptions' => ['class'=>'custom_width'],
                    'contentOptions' => ['class'=>'custom_width'],
					'filterType'=>GridView::FILTER_SELECT2,
					'filter'=> Customer::getSelectMap(),
					'filterWidgetOptions' => [
						'pluginOptions'=>['allowClear'=>true],
						'options'=>['multiple'=>false],
					],
					'value'=> function($model){
                        $temp_name = (isset($model->customer->commercial_name) && !empty($model->customer->commercial_name))? $model->customer->name.' - '.$model->customer->commercial_name : $model->customer->name;
                        return Html::a($temp_name,['/customer/view','id'=>$model->customer_id],['target'=>'_blank','data-pjax'=>0]);
                    },
					'filterInputOptions'=>['placeholder'=> '------'],
					'hAlign'=>'center',
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
					'attribute'=>'currency_id',
                    'format' => 'html',
					'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
					'filterType'=>GridView::FILTER_SELECT2,
					'filter'=> Currency::getSelectMap(),
					'filterWidgetOptions' => [
						'pluginOptions'=>['allowClear'=>true],
						'options'=>['multiple'=>false],
					],
					'value'=> 'currency.symbol',
					'filterInputOptions'=>['placeholder'=> '------'],
					'hAlign'=>'center',
				],

                [
                    'attribute'=>'condition_sale_id',
                    'format' => 'html',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'filterType'=>GridView::FILTER_SELECT2,
                    'filter'=> ConditionSale::getSelectMap(),
                    'filterWidgetOptions' => [
                        'pluginOptions'=>['allowClear'=>true],
                        'options'=>['multiple'=>false],
                    ],
                    'value'=> 'conditionSale.name',
                    'filterInputOptions'=>['placeholder'=> '------'],
                    'hAlign'=>'center',
                ],
                /*
				[
                    'attribute'=>'sellers',
                    'headerOptions' => ['class'=>'custom_width'],
                    'contentOptions' => ['class'=>'custom_width'],
					'hAlign'=>'center',
                    'value' => function($data){
                        return SellerHasInvoice::getSellerStringByInvoice($data->id);
                    },
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
                /*               
                [
                    'attribute'=>'collector_id',
                    'format' => 'html',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'filterType'=>GridView::FILTER_SELECT2,
                    'filter'=> User::getSelectMapAgents(true,true),
                    'filterWidgetOptions' => [
                        'pluginOptions'=>['allowClear'=>true],
                        'options'=>['multiple'=>false],
                    ],
                    'value'=> function($model){
                        return (isset($model->collector_id) && !empty($model->collector_id))? User::getFullNameByUserId($model->collector_id) : null;
                    },
                    'filterInputOptions'=>['placeholder'=> '------'],
                    'hAlign'=>'center',
                ], 
                */               

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
                    'attribute'=>'status',
                    'format' => 'html',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'filterType'=>GridView::FILTER_SELECT2,
                    'filter'=> UtilsConstants::getInvoiceStatusSelectType(),
                    'filterWidgetOptions' => [
                        'pluginOptions'=>['allowClear'=>true],
                        'options'=>['multiple'=>false],
                    ],
                    'value'=> function($data){
                        return UtilsConstants::getInvoiceStatusSelectType($data->status);
                    },
                    'filterInputOptions'=>['placeholder'=> '------'],
                    'hAlign'=>'center',
                ],

                [
                    'attribute'=>'status_hacienda',
                    'format' => 'html',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'filterType'=>GridView::FILTER_SELECT2,
                    'filter'=> UtilsConstants::getHaciendaStatusSelectType(),
                    'filterWidgetOptions' => [
                        'pluginOptions'=>['allowClear'=>true],
                        'options'=>['multiple'=>false],
                    ],
                    'value' => function ($model) {
                        $color = '';
                        $status = (int) $model->status_hacienda;

                        if ($status === UtilsConstants::HACIENDA_STATUS_NOT_SENT)
                        {
                            $color = 'gray';
                        }
                        elseif ($status === UtilsConstants::HACIENDA_STATUS_RECEIVED)
                        {
                            $color = 'orange';
                        }
                        elseif ($status === UtilsConstants::HACIENDA_STATUS_ACCEPTED)
                        {
                            $color = 'blue';
                        }
                        elseif ($status === UtilsConstants::HACIENDA_STATUS_REJECTED)
                        {
                            $color = 'red';
                        }
                        elseif ($status === UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE)
                        {
                            $color = 'red';
                        }
                        elseif ($status === UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE_PARTIAL)
                        {
                            $color = 'blue';
                        }                           
                        elseif ($status === UtilsConstants::HACIENDA_STATUS_DEBIT_NOTE)
                        {
                            $color = 'red';
                        }
                        elseif ($status === UtilsConstants::HACIENDA_STATUS_ANULATE)
                        {
                            $color = 'black';
                        }
                        elseif ($status === UtilsConstants::HACIENDA_STATUS_CANCELLED)
                        {
                            $color = 'blue';
                        }

                        return " <small class=\"badge bg-".$color."\"></i> ". UtilsConstants::getHaciendaStatusSelectType($status,true)."</small>";
                    },
                    'filterInputOptions'=>['placeholder'=> '------'],
                    'hAlign'=>'center',
                ],

                $custom_elements_gridview->getActionColumn(),

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

                $custom_elements_gridview->getCheckboxColumn(),

            ],

            'toolbar' =>  $custom_elements_gridview->getToolbar(),

            'panel' => $custom_elements_gridview->getPanel(),

            'toggleDataOptions' => $custom_elements_gridview->getTogleDataOptions(),
        ]); ?>
    </div>

    <div id="panel-form-enviar"></div>

<?php
    $url = Url::to([$controllerId.'multiple_delete'], GlobalFunctions::URLTYPE);
    $js = Footer_Bulk_Delete::getFooterBulkDelete($url,'grid_invoice');
    $this->registerJs($js, View::POS_READY);
?>

<?php
// Register action buttons js
$this->registerJs('
    $(document).ready(function()
    {
        function init_click_handlers(){
                $("a.btn-send-factura").click(function(e) {
                    e.preventDefault();
                    var selectedId = $("#grid_invoice").yiiGridView("getSelectedRows");
                    if(selectedId.length == 0) {
                        bootbox.alert("Seleccione un elemento"); 
                    } else if(selectedId.length>1){
                        bootbox.alert("Solo se permite enviar una factura a la vez"); 				
                    } else {
                        $.LoadingOverlay("show");					
                        var url = "'.Url::to(['enviar-factura-hacienda'], GlobalFunctions::URLTYPE).'?id="+selectedId[0];						
                        $.ajax({
                            type: \'GET\',
                            url : url,
                            data : {},
                            success : function(response) {	
                                $.LoadingOverlay("hide");		
                                $("#text-informacion").html(response.mensaje);	
                                $.pjax.reload({container:"#grid_invoice-pjax"});			
                                $.notify({
                                        "message": response.mensaje,
                                        "icon": "glyphicon glyphicon-ok-sign",
                                        "title": response.titulo,						
                                        "showProgressbar": false,
                                        "url":"",						
                                        "target":"_blank"},{"type": response.type}
                                );						
                                
                            },
                            error: function(XMLHttpRequest, textStatus, errorThrown) {
                                $.LoadingOverlay("hide");	
                                $.pjax.reload({container:"#grid_invoice-pjax"});
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
                });			
        
                $("a.btn-get-estado-factura").click(function(e) {
                    e.preventDefault();
                    var selectedId = $("#grid_invoice").yiiGridView("getSelectedRows");
                    if(selectedId.length == 0) {
                        bootbox.alert("Seleccione un elemento"); 
                    } else if(selectedId.length>1){
                        bootbox.alert("Solo se permite enviar una factura a la vez"); 				
                    } else {
        
                        //$("#text-informacion").html("Consultando estado de la factura en Hacienda");
                        //$("#panel-informacion").show(500);					
                        $.LoadingOverlay("show");				
                        var url = "'.Url::to(['get-estado-factura-hacienda'], GlobalFunctions::URLTYPE).'?id="+selectedId[0];						
                        $.ajax({
                            type: \'GET\',
                            url : url,
                            data : {},
                            success : function(response) {	
                                $.LoadingOverlay("hide");	
                                $("#text-informacion").html(response.mensaje);	
                                $.pjax.reload({container:"#grid_invoice-pjax"});			
                                $.notify({
                                        "message": response.mensaje,
                                        "icon": "glyphicon glyphicon-ok-sign",
                                        "title": response.titulo,						
                                        "showProgressbar": false,
                                        "url":"",						
                                        "target":"_blank"},{"type": response.type}
                                );						
                                
                            },
                            error: function(XMLHttpRequest, textStatus, errorThrown) {
                                $.LoadingOverlay("hide");
                                var texto = $("#text-informacion").html() + ". Ha ocurrido un error.";
                                $("#text-informacion").html(texto);
                                $.pjax.reload({container:"#grid_invoice-pjax"});
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
                });		
                
                $("a.btn-nota-credito").click(function(e) {
                    e.preventDefault();        
                    var selectedId = $("#grid_invoice").yiiGridView("getSelectedRows");
                    if(selectedId.length == 0) {
                        bootbox.alert("Seleccione un elemento"); 
                    } else if(selectedId.length>1){
                        bootbox.alert("Solo puede seleccionar un elemento"); 
                    } else {
                        var url = "'.Url::to(['/credit-note/create'], GlobalFunctions::URLTYPE).'?invoice_id="+selectedId[0];
                        window.location.href= url;
                    }
                });	
                
                $("a.btn-nota-debito").click(function(e) {
                    e.preventDefault();        
                    var selectedId = $("#grid_invoice").yiiGridView("getSelectedRows");
                    if(selectedId.length == 0) {
                        bootbox.alert("Seleccione un elemento"); 
                    } else if(selectedId.length>1){
                        bootbox.alert("Solo puede seleccionar un elemento"); 
                    } else {
                        var url = "'.Url::to(['/debit-note/create'], GlobalFunctions::URLTYPE).'?invoice_id="+selectedId[0];
                        window.location.href= url;
                    }
                });				
                
                $("a.btn-sendemail-factura").click(function(e) {
                    e.preventDefault();
                    var selectedId = $("#grid_invoice").yiiGridView("getSelectedRows");
                    if(selectedId.length == 0) {
                        bootbox.alert("Seleccione un elemento"); 
                    } else if(selectedId.length>1){
                        bootbox.alert("Solo se permite enviar una factura a la vez"); 				
                    }
                    else
                    {
                        $("#panel-grid").hide(500);
                        $("#panel-form-enviar").show(500);
                                    
                        var url = "'.Url::to(['/invoice/enviar-factura-email'], GlobalFunctions::URLTYPE).'?id="+selectedId[0];
                        //window.location.href= url;
                        $.LoadingOverlay("show");											
                        $.ajax({
                            type: \'GET\',
                            url : url,
                            data : {},
                            success : function(data) {
                                $.LoadingOverlay("hide");
                                $("#panel-form-enviar").html(data);
                            },
                            error: function(XMLHttpRequest, textStatus, errorThrown) {
                                $.LoadingOverlay("hide");
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
                });		
 
                $("a.btn-imp-tickets").click(function(e) {
                    e.preventDefault();        
                    var selectedId = $("#grid_invoice").yiiGridView("getSelectedRows");
                    if(selectedId.length == 0) {
                        bootbox.alert("Seleccione un elemento"); 
                    } else {
                        var url = "'.Url::to(['/invoice/imp-tickets'], GlobalFunctions::URLTYPE).'?ids="+selectedId;
                        window.location.href= url;
                    }
                });	

                $("a.btn-pdf-preparation").click(function(e) {
                    e.preventDefault();
                    var selectedId = $("#grid_invoice").yiiGridView("getSelectedRows");
        
                    if(selectedId.length == 0) {
                        bootbox.alert("Seleccione al menos un elemento"); 
                    } else {
                        var url = "'.Url::to(['/invoice/preparation_pdf'], GlobalFunctions::URLTYPE).'?ids="+selectedId;
                        window.open(url,"_blank");
                    }
                });	                
        }
        
    init_click_handlers(); //first run
    $("#grid_invoice-pjax").on("pjax:success", function() {
      init_click_handlers(); //reactivate links in grid after pjax update
    });
});
');

?>
