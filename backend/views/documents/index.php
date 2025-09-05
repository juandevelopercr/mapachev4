<?php

use yii\helpers\Html;
use common\widgets\GridView;
use mdm\admin\components\Helper;
use yii\web\View;
use yii\helpers\Url;
use backend\models\nomenclators\UtilsConstants;
use backend\components\Footer_Bulk_Delete;
use backend\components\Custom_Settings_Column_GridView;
use backend\models\nomenclators\ConditionSale;
use common\models\GlobalFunctions;
use yii\helpers\BaseStringHelper;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\DocumentsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = Yii::t('backend', 'Documentos Electrónicos');
$this->params['breadcrumbs'][] = $this->title;

$create_button='';
?>

<?php 
	if (Helper::checkRoute($controllerId . 'create')) {
		$create_button = Html::a('<i class="fa fa-plus"></i> '.Yii::t('backend', 'Crear'), ['create'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Crear').' '.Yii::t('backend', 'Documento')]);
	}

    $enviarHacienda = Html::a('<i class="fa fa-exchange"></i> Enviar a Hacienda',['#'], ['class' => 'btn btn-default btn-flat margin btn-send-documento']);

	$custom_elements_gridview = new Custom_Settings_Column_GridView($create_button.' '.$enviarHacienda,$dataProvider);


    $panels = [        
        'after' => Html::a('<i class="fa fa-envelope"></i> Enviar XML de Hacienda',['#'], ['class' => 'btn btn-default btn-sendemail-documento']).'&nbsp;&nbsp;'.
            Html::a('<i class="glyphicon glyphicon-transfer"></i> Obtener Estado en Hacienda',['#','p_reset'=>true], ['class' => 'btn btn-default btn-get-estado-documento']),
    ];
    
    $custom_elements_gridview->setPanel($panels); 

?>
    <div class="box-body">
        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
        <?= GridView::widget([
            'id'=>'grid-documentos',
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
                        return $data->consecutive;
                    }
                ],

                [
                    'attribute'=>'xml_emission_date',
                    'value' => function($data){
                        return GlobalFunctions::formatDateToShowInSystem($data->xml_emission_date);
                    },
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'hAlign'=>'center',
                    'filterType' => GridView::FILTER_DATE_RANGE,
                    'filterWidgetOptions' => ([
                        'model' => $searchModel,
                        'attribute' => 'xml_emission_date',
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
                    'attribute'=>'reception_date',
                    'value' => function($data){
                        return GlobalFunctions::formatDateToShowInSystem($data->reception_date);
                    },
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'hAlign'=>'center',
                    'filterType' => GridView::FILTER_DATE_RANGE,
                    'filterWidgetOptions' => ([
                        'model' => $searchModel,
                        'attribute' => 'reception_date',
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
                    'attribute'=>'key',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'hAlign'=>'center',
                    'format'=> 'html',
                    'value' => function ($data) {
                        $consecutivo = substr($data->key, 21);
                        $consecutivo = substr($consecutivo, 0, 20);
                        return $consecutivo;
                    }
                ],   
                 
                [
                    'attribute'=>'key',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'hAlign'=>'center',
                    'format'=> 'html',
                ],   
                        
                [
                    'attribute' => 'document_type',
                    'format' => 'html',
                    'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => UtilsConstants::getDocumentType(),
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                        'options' => ['multiple' => false],
                    ],
                    'value' => function ($data) {

                        $color = 'green'; 
                        if ($data->document_type == 'FE')
                            $color = 'red';
                        return " <small class=\"badge bg-" . $color . "\"></i> " . UtilsConstants::getDocumentType($data->document_type, true) . "</small>";
                    },
                    'filterInputOptions' => ['placeholder' => '------'],
                    'hAlign' => 'center',
                ],  
                [
                    'attribute'=>'condition_sale',
                    'format' => 'html',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'filterType'=>GridView::FILTER_SELECT2,
                    'filter'=> ['01'=>'Contado', '02'=>'Crédito'],
                    'filterWidgetOptions' => [
                        'pluginOptions'=>['allowClear'=>true],
                        'options'=>['multiple'=>false],
                    ],
                    'value'=>function($data){
                        $str = '';
                        if ($data->condition_sale == '01')
                            $str = 'Contado';
                        else
                            $str = 'Crédito'; 
                        return $str;   
                    },
                    'filterInputOptions'=>['placeholder'=> '------'],
                    'hAlign'=>'center',
                ],                

                [
                    'label'=>'Documentos',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle col-documento'], 
                    'hAlign'=>'center',
                    'format'=> 'raw',                    
                    'value' => function ($data) {
                        $url = urldecode(Url::toRoute(['update',
                            'id' => $data->id,
                        ]));
                        $color = 'green';
                        $color1 = 'blue';
                        $texto = $data->key;
                        
                        $url_xml = $data->getFileUrlXML();
                        $url_pdf = $data->getFileUrlPDF();
                        $url_xml_hacienda = Yii::getAlias('/backend/web/documents/'.$data->url_ahc);
                        $url_xml_hacienda_verificar = Yii::getAlias('@backend/web/documents/'.$data->url_ahc);					
                        $tipo = '';
                        switch ($data->document_type)
                        {
                            case 'FE':$tipo = 'FE';
                                      break;
                            case 'ND':$tipo = 'ND';
                                      break;
                            case 'NC':$tipo = 'NC';
                                      break;
                            case 'TE':$tipo = 'TE';
                                      break;
                            case 'MR':$tipo = 'MR';
                                      break;
                            case 'MR':$tipo = 'MR';
                                      break;
                            case 'MR':$tipo = 'MR';
                                      break;
                            case 'FEC':$tipo = 'FEC';
                                      break;
                            case 'FEE':$tipo = 'FEE';								  
                        }
                        $btnpdf = '';
                        $btnxml = '';
                        
                        if (!is_null($url_pdf) && !empty($url_pdf))
                            $btnpdf = "<a href=\"".$url_pdf."\" title=\"".$data->key."\" data-method=\"post\" data-pjax=\"0\" target=\"_blank\" class=\"badge bg-".$color1."\"><i class=\"fa fa-fw fa-download\"></i> ".$tipo."-PDF</a>";
    
                        if (!is_null($url_xml) && !empty($url_xml))
                            $btnxml = "<a href=\"".$url_xml."\" title=\"".$data->key."\" data-method=\"post\" data-pjax=\"0\" target=\"_blank\" class=\"badge bg-".$color1."\"><i class=\"fa fa-fw fa-download\"></i> ".$tipo."-XML</a>";
        
                        $btnxml_hacienda = '';
                        if (!is_null($data->url_ahc) && !empty($data->url_ahc))	
                        {	
                            $url_xml_hacienda_verificar = Yii::getAlias('@backend/web/uploads/xmlh/'.$data->url_ahc);
                            $url = Yii::getAlias('/backend/web/uploads/xmlh/'.$data->url_ahc);
                            if (file_exists($url_xml_hacienda_verificar))
                            {
                                $btnxml_hacienda = "<a href=\"".$url."\" title=\"".$data->key."\" data-method=\"post\" data-pjax=\"0\" target=\"_blank\" class=\"badge bg-".$color1."\"><i class=\"fa fa-fw fa-download\"></i> ".$tipo."-XML-H</a>";
                            }
                        }
                            
                        return $btnpdf.' '.$btnxml.' '.$btnxml_hacienda;						                        
                    }                    
                ],	                

                [
                    'attribute'=>'transmitter',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'], 
                    'hAlign'=>'center',
                    'format'=> 'html',                    
                    'value' => function ($data) {
                        $url = urldecode(Url::toRoute(['update',
                            'id' => $data->id,
                        ]));
                        return Html::a($data->transmitter, $url, ['data-pjax'=>0, 'data-method'=>"post"]);
                    }                    
                ],	
                [
                    'attribute'=>'total_tax',
                    'contentOptions'=>['class'=>'kv-align-right kv-align-middle'],
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'format' => ['decimal', 2],
                    'pageSummary' => true,
                    'pageSummaryFunc' => GridView::F_SUM,
                ],                   
                [
                    'attribute'=>'total_invoice',
                    'contentOptions'=>['class'=>'kv-align-right kv-align-middle'],
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'format' => ['decimal', 2],
                    'pageSummary' => true,
                    'pageSummaryFunc' => GridView::F_SUM,
                ],   
                [
                    'attribute'=>'currency',
                    'contentOptions'=>['class'=>'kv-align-center kv-align-middle'], 
                    'filterType'=>GridView::FILTER_SELECT2,
                    'filter'=>['CRC'=>'CRC', 'USD'=>'USD'],
                    'filterWidgetOptions'=>[
                        'pluginOptions'=>['allowClear'=>true],
                        'options'=>['multiple'=>false],
                    ],
                    'value'=> function($data){
                        return $data->currency;	
                    },
                    'filterInputOptions'=>['placeholder'=>Yii::t('app', '-- Selecione --')],
                    'hAlign'=>'center',
                ],                             

                [
                    'attribute'=>'status',
                    'contentOptions'=>['class'=>'kv-align-center kv-align-middle'], 
                    'filterType'=>GridView::FILTER_SELECT2,
                    'filter'=> UtilsConstants::getDocumentStatusSelectType(),
                    'filterWidgetOptions'=>[
                        'pluginOptions'=>['allowClear'=>true],
                        'options'=>['multiple'=>false],
                    ],
                    'format'=>'html',
                    'value'=>function ($model, $key, $index, $widget) {
                            $color = '';
                            $checkclass = '';
                            if ($model->status == UtilsConstants::HACIENDA_STATUS_ACEPTADO_RECEPTOR)
                            {								
                                $color = 'info';
                                $checkclass = "row-pendiente";
                            }
                            else
                            if ($model->status == UtilsConstants::HACIENDA_STATUS_ACEPTADO_PARCIAL_RECEPTOR)									
                            {
                                $color = 'info';
                                $checkclass = "row-pendiente";
                            }
                            else
                            if ($model->status == UtilsConstants::HACIENDA_STATUS_RECHAZADO_RECEPTOR)									
                            {
                                $color = 'info';
                                $checkclass = "row-pendiente";
                            }
                            else						
                            if ($model->status == UtilsConstants::HACIENDA_STATUS_RECIBIDO_HACIENDA)
                            {								
                                $color = 'warning';
                                $checkclass = "row-pendiente";
                            }
                            else
                            if ($model->status == UtilsConstants::HACIENDA_STATUS_RECIBIDO_PARCIAL_HACIENDA)
                            {								
                                $color = 'warning';
                                $checkclass = "row-pendiente";
                            }
                            else
                            if ($model->status == UtilsConstants::HACIENDA_STATUS_RECIBIDO_RECHAZADO_HACIENDA)
                            {								
                                $color = 'warning';
                                $checkclass = "row-pendiente";
                            }						
                            else						
                            if ($model->status == UtilsConstants::HACIENDA_STATUS_ACEPTADO_HACIENDA)
                                $color = 'custom-ceptada';
                            else
                            if ($model->status == UtilsConstants::HACIENDA_STATUS_ACEPTADO_PARCIAL_HACIENDA)
                                $color = 'custom-ceptada';
                            else						
                            if ($model->status == UtilsConstants::HACIENDA_STATUS_RECHAZADO_HACIENDA)						
                                $color = 'danger';

                            $name = '-';    
                            if (!is_null($model->status) && !empty($model->status))    
                                $name = UtilsConstants::getDocumentStatusSelectType($model->status);

                            return " <small class=\"label label-".$color." ".$checkclass."\"></i> ".$name."</small>";  
                    },				
                    'filterInputOptions'=>['placeholder'=>Yii::t('app', '-- Selecione --')],
                    'hAlign'=>'center',
                    'pageSummary'=>'',
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
    $url = Url::to([$controllerId.'multiple_delete'], GlobalFunctions::URLTYPE);
    $js = Footer_Bulk_Delete::getFooterBulkDelete($url);
    $this->registerJs($js, View::POS_READY);
?>

<div id="panel-form-enviar"></div>  

<?php 
// Register action buttons js
$this->registerJs('
$(document).ready(function()
{
	function init_click_handlers(){				       		
		$("a.btn-get-estado-documento").click(function(e) {
			e.preventDefault();
			var selectedId = $("#grid-documentos").yiiGridView("getSelectedRows");
            if(selectedId.length == 0) {
                bootbox.alert("Seleccione un elemento"); 
            } else if(selectedId.length>1){
				bootbox.alert("Solo se permite enviar una factura a la vez"); 				
            } else {
				var url = "'.Url::to(['get-estado-documento-hacienda'], GlobalFunctions::URLTYPE).'?id="+selectedId[0];						
				$.LoadingOverlay("show");
				$.ajax({
					type: \'GET\',
					url : url,
					data : {},
					success : function(response) {	
						$.LoadingOverlay("hide");
						$.pjax.reload({container:"#grid-documentos-pjax"});			
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
						$.pjax.reload({container:"#grid-documentos-pjax"});
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
		
		$("a.btn-sendemail-documento").click(function(e) {
			e.preventDefault();
			var selectedId = $("#grid-documentos").yiiGridView("getSelectedRows");
            if(selectedId.length == 0) {
                bootbox.alert("Seleccione un elemento"); 
            } else if(selectedId.length>1){
				bootbox.alert("Solo se permite enviar una factura a la vez"); 				
            }
			else
			{							
				var url = "'.Url::to(['/documents/enviar-documento-email'], GlobalFunctions::URLTYPE).'?id="+selectedId[0];
				//window.location.href= url;	
				$.LoadingOverlay("show");						
				$.ajax({
					type: \'GET\',
					url : url,
					data : {},
					success : function(data) {
						$.LoadingOverlay("hide");
						if (data["file_no_found"] == true)
						{
							bootbox.alert("No existe el xml de respuesta de Hacienda"); 								
						}
						else
						{
							$("#panel-grid").hide(500);
							$("#panel-form-enviar").show(500);
							$("#panel-form-enviar").html(data);
						}
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
		
		$("a.btn-send-documento").click(function(e) {
			e.preventDefault();
			
			var selectedId = $("#grid-documentos").yiiGridView("getSelectedRows");
            if(selectedId.length == 0) {
                bootbox.alert("Seleccione un elemento"); 
            } else if(selectedId.length>1){
                bootbox.alert("Solo se permite enviar una factura a la vez"); 				
            } else {
				var url = "'.Url::to(['enviar-documento-hacienda'], GlobalFunctions::URLTYPE).'?id="+selectedId[0];		
				$.LoadingOverlay("show");				
				$.ajax({
					type: \'GET\',
					url : url,
					data : {},
					success : function(response) {	
						//$("#text-informacion").html(response.mensaje);	
						$.LoadingOverlay("hide");						
						$.pjax.reload({container:"#grid-documentos-pjax"});
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
						//var texto = $("#text-informacion").html() + ". Ha ocurrido un error.";
						//$("#text-informacion").html(texto);
						$.pjax.reload({container:"#grid-facturas-pjax"});
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
		
	}
	
    init_click_handlers(); //first run
    $("#grid-documentos-pjax").on("pjax:success", function() {
      init_click_handlers(); //reactivate links in grid after pjax update
    });
});
');

?>

<?php
/*
$js = <<<JS
// get the form id and set the event
		function cron() {
			var ids = [];
			$(".kv-grid-table").find(".row-pendiente").each(function(e) {
				ids.push($(this).parent().parent().attr("data-key"));				
			});
			if (ids.length > 0)
			{
				var url = '/backend/facturacion/documentos-recibidos/get-estado-documento-automatico';						
				$.ajax({
					type: 'GET',
					url : url,
					data : {ids:ids},
					success : function(response) {	
						if (response.datos.length > 0)		
						{
							var lista = response.datos;
							lista.forEach( function(valor, indice, array) {

								tr = $('.kv-grid-table tbody tr[data-key="' + valor["id"] + '"]');	
								
								tr.find(".row-pendiente:first").each(function(e){
									var td = $(this).parent();
									$(td).html("");
									$(td).html(valor["html"]); 
								});

								tr.find(".col-documento:first").html(valor["btnhtml"]); 
							});
						}
					},
					error: function(XMLHttpRequest, textStatus, errorThrown) {
		
					}						
				});	
			}
		}
		setInterval(cron, 9000);	

JS;
$this->registerJs($js);
*/
?>