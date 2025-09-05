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
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use backend\models\nomenclators\RouteTransport;
use backend\models\business\SellerHasDebitNote;
use common\models\User;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\DebitNoteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = Yii::t('backend', 'Notas de débito');
$this->params['breadcrumbs'][] = $this->title;

$create_button='';
?>

<?php 

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
                return Html::a('<i class="glyphicon glyphicon-pencil"></i>', Url::to(['/debit-note/update', 'id' => $model->id], GlobalFunctions::URLTYPE), $options);
            }
        },
//        'corregir' => function($url, $model) {
//            $options = [
//                'title' => Yii::t('backend', 'Corregir documento'),
//                'class' => 'btn btn-xs btn-default btn-flat',
//                'aria-label' => Yii::t('backend', 'Corregir documento'),
//                'data-method' => 'post',
//                'data-pjax' => '0',
//                'data-toggle' => 'tooltip',
//                'data-confirm' => Yii::t('backend', '¿Seguro desea corregir este documento? Este proceso creará un duplicado del documento y anulará el documento actual'),
//
//            ];
//            if ($model->status_hacienda === UtilsConstants::HACIENDA_STATUS_REJECTED)
//            {
//                return Html::a('<i class="fa fa-remove text-danger"></i>', ['/debit-note/correction', 'id' => $model->id], $options);
//            }
//        },
//        'pdf_contingencia' => function($url, $model) {
//            $options = [
//                'title' => Yii::t('backend', 'PDF-Contingencia'),
//                'class' => 'btn btn-xs btn-default btn-flat',
//                'aria-label' => Yii::t('backend', 'PDF-Contingencia'),
//                'data-method' => 'post',
//                'data-pjax' => '0',
//                'data-toggle' => 'tooltip',
//                'target' => '_blank'
//            ];
//            return Html::a('<i class="fa fa-file-text-o"></i>', ['/debit-note/viewpdfcontingencia', 'id' => $model->id], $options);
//        },
        'pdf' => function($url, $model) {
            $options = [
                'title' => Yii::t('backend', 'ND-PDF'),
                'class' => 'btn btn-xs btn-default btn-flat',
                'aria-label' => Yii::t('backend', 'ND-PDF'),
                'data-method' => 'post',
                'data-pjax' => '0',
                'data-toggle' => 'tooltip',
                'target' => '_blank'
            ];
            return Html::a('<i class="fa fa-file-pdf-o"></i>', Url::to(['/debit-note/viewpdf', 'id' => $model->id], GlobalFunctions::URLTYPE), $options);
        },
        'print' => function($url, $model) {
            $options = [
                'title' => Yii::t('backend', 'ND-IMP'),
                'class' => 'btn btn-xs btn-default btn-flat',
                'aria-label' => Yii::t('backend', 'ND-IMP'),
                'data-method' => 'post',
                'data-pjax' => '0',
                'data-toggle' => 'tooltip',
                'target' => '_blank'
            ];
            return Html::a('<i class="fa fa-print"></i>', Url::to(['/debit-note/printpdf', 'id' => $model->id], GlobalFunctions::URLTYPE), $options);
        },
//        'ticket' => function($url, $model) {
//            $options = [
//                'title' => Yii::t('backend', 'TE-PDF'),
//                'class' => 'btn btn-xs btn-default btn-flat',
//                'aria-label' => Yii::t('backend', 'TE-PDF'),
//                'data-method' => 'post',
//                'data-pjax' => '0',
//                'data-toggle' => 'tooltip',
//                'target' => '_blank'
//            ];
//            return Html::a('<i class="fa fa-ticket"></i>', ['/debit-note/viewticketpdf', 'id' => $model->id], $options);
//        },
        'xml' => function($url, $model) {
            $options = [
                'title' => Yii::t('backend', 'ND-XML'),
                'class' => 'btn btn-xs btn-default btn-flat',
                'aria-label' => Yii::t('backend', 'ND-XML'),
                'data-method' => 'post',
                'data-pjax' => '0',
                'data-toggle' => 'tooltip',
                'target' => '_blank'
            ];
            return Html::a('<i class="fa fa-file-code-o"></i>', Url::to(['/debit-note/viewxml', 'id' => $model->id], GlobalFunctions::URLTYPE), $options);
        },
        'xmlh' => function($url, $model) {
            $options = [
                'title' => Yii::t('backend', 'ND-MH-XML'),
                'class' => 'btn btn-xs btn-default btn-flat',
                'aria-label' => Yii::t('backend', 'ND-MH-XML'),
                'data-method' => 'post',
                'data-pjax' => '0',
                'data-toggle' => 'tooltip',
                'target' => '_blank'
            ];

            $url_xml_hacienda_verificar = Yii::getAlias('@backend/web/uploads/xmlh/ND-MH-'.$model->key.'.xml');
            $url = Yii::getAlias('/backend/web/uploads/xmlh/ND-MH-'.$model->key.'.xml');
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
                return Html::a('<i class="glyphicon glyphicon-trash"></i>', Url::to(['/debit-note/delete', 'id' => $model->id], GlobalFunctions::URLTYPE), $options);
            }
        },
    ];
	$custom_elements_gridview = new Custom_Settings_Column_GridView($create_button,$dataProvider,['view','update','corregir','pdf_contingencia','pdf','print','ticket','xml','xmlh','delete'],$custom_buttons);

$panels = [
    'before' =>
        '<span style="float: right; margin: 10px;">'.
        Html::a('<i class="glyphicon glyphicon-transfer"></i> Enviar a Hacienda',['#'], ['class' => 'btn btn-default btn-send-factura']).'</span>'
    ,
    'after' => Html::a('<i class="fa fa-envelope"></i> Enviar Nota y XML',['#'], ['class' => 'btn btn-default btn-sendemail-factura']).'&nbsp;&nbsp;'.
        Html::a('<i class="glyphicon glyphicon-transfer"></i> Obtener Estado en Hacienda',['#','p_reset'=>true], ['class' => 'btn btn-default btn-get-estado-factura']),
];
	$custom_elements_gridview->setPanel($panels);
?>

    <div class="box-body" id="panel-grid">
        <?= GridView::widget([
            'id'=>'grid_debitnote',
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
            'hover' => true,
            'persistResize'=>true,
            'showPageSummary' => true,
            'filterModel' => $searchModel,
            'as filterBehavior' => \thrieu\grid\FilterStateBehavior::className(),	
            'columns' => [

				$custom_elements_gridview->getSerialColumn(),

				[
					'attribute'=>'consecutive',
					'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
					'hAlign'=>'center',
					'format'=> 'html',
					'value' => function ($data) {
						return Html::a($data->consecutive, ['/debit-note/update', 'id' => $data->id]);
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
                        return SellerHasDebitNote::getSellerStringByDebitNote($model->id);
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

//                [
//                    'attribute'=>'status',
//                    'format' => 'html',
//                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
//                    'filterType'=>GridView::FILTER_SELECT2,
//                    'filter'=> UtilsConstants::getInvoiceStatusSelectType(),
//                    'filterWidgetOptions' => [
//                        'pluginOptions'=>['allowClear'=>true],
//                        'options'=>['multiple'=>false],
//                    ],
//                    'value'=> function($data){
//                        return UtilsConstants::getInvoiceStatusSelectType($data->status);
//                    },
//                    'filterInputOptions'=>['placeholder'=> '------'],
//                    'hAlign'=>'center',
//                ],

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
                    'label'=>'Monto',
                    'contentOptions'=>['class'=>'kv-align-right kv-align-middle'], // <-- right here
                    'filter'=> '',
                    'format'=>['decimal', 2],
                    'hAlign'=>'right',
                    'value'=>function ($model, $key, $index, $widget) {
                        return $model->getTotalAmount();
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
    $js = Footer_Bulk_Delete::getFooterBulkDelete($url,'grid_debitnote');
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
                    var selectedId = $("#grid_debitnote").yiiGridView("getSelectedRows");
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
                                $.pjax.reload({container:"#grid_debitnote-pjax"});			
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
                                $.pjax.reload({container:"#grid_debitnote-pjax"});
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
                    var selectedId = $("#grid_debitnote").yiiGridView("getSelectedRows");
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
                                $.pjax.reload({container:"#grid_debitnote-pjax"});			
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
                                $.pjax.reload({container:"#grid_debitnote-pjax"});
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
           
                $("a.btn-sendemail-factura").click(function(e) {
                    e.preventDefault();
                    var selectedId = $("#grid_debitnote").yiiGridView("getSelectedRows");
                    if(selectedId.length == 0) {
                        bootbox.alert("Seleccione un elemento"); 
                    } else if(selectedId.length>1){
                        bootbox.alert("Solo se permite enviar una factura a la vez"); 				
                    }
                    else
                    {
                        $("#panel-grid").hide(500);
                        $("#panel-form-enviar").show(500);
                                    
                        var url = "'.Url::to(['/debit-note/enviar-factura-email'], GlobalFunctions::URLTYPE).'?id="+selectedId[0];
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
                    var selectedId = $("#grid_debitnote").yiiGridView("getSelectedRows");
                    if(selectedId.length == 0) {
                        bootbox.alert("Seleccione un elemento"); 
                    } else {
                        var url = "'.Url::to(['/debit-note/imp-tickets'], GlobalFunctions::URLTYPE).'?ids="+selectedId;
                        window.location.href= url;
                    }
                });	
        }
        
    init_click_handlers(); //first run
    $("#grid_debitnote-pjax").on("pjax:success", function() {
      init_click_handlers(); //reactivate links in grid after pjax update
    });
});
');

?>
