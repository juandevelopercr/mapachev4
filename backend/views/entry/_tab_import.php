<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\dynagrid\DynaGrid;
use yii\helpers\Url;
use backend\models\business\ItemImported;
use backend\models\business\Product;
use backend\models\nomenclators\UnitType;
use common\models\GlobalFunctions;
use common\models\User;

/* @var $this yii\web\View */
/* @var $searchModel \backend\models\business\ItemImportedSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$title = Yii::t('backend', 'Items importados');
?>
<?php
if ($model->isNewRecord) {
    $label_update = Yii::t('backend', 'Para importar items, debe crear antes la entrada');

    echo "<br /><div class=\"alert-warning alert fade in\">
			<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>
			<i class=\"icon fa fa-warning\"></i>$label_update
			</div>";
}
else
{
?>
    <div class="item-entry-index" style="margin-top:15px;" id="panel-grid-item_imported">
      <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
      
    <?php 
         $toolbars = [
            //['content' => '{dynagrid}'],
             [
                 'content' =>
                     Html::a('<i class="fa fa-refresh"></i> '.Yii::t('backend','Resetear'), ['index'], ['class' => 'btn btn-default btn-flat btn-refresh_item_imported','title'=> Yii::t('backend','Resetear listado'), 'data-toggle' => 'tooltip', 'data-pjax' => 1]),
             ],
            //'{export}',
            '{toggleData}',
        ];
        
        $panels = [
            'heading'    => '<h3 class="panel-title"><i class="fa fa-file-text-o"></i></h3>',
                    'type' => 'default',
                    'before' =>
//                        '<span style="margin-right: 5px;">'.
//                        Html::a('<i class="glyphicon glyphicon-plus text-primary"></i> '.Yii::t('backend', 'Importar'),
//                            ['#'], ['class' => 'btn btn-create-import btn-default']
//                        ).'</span>'.
                        '<span style="margin-right: 5px;">'.
                        Html::a('<i class="glyphicon glyphicon-ok text-success"></i> '.Yii::t('backend', 'Aprobar'),
                            ['#'], ['class' => 'btn btn-aprov_item_imported btn-default']
                        ).'</span>'.
                        '<span style="margin-right: 5px;">'.
                        Html::a('<i class="glyphicon glyphicon-tag text-warning"></i> '.Yii::t('backend', 'Asignar ubicación'),
                            ['#'], ['class' => 'btn btn-location_item_imported btn-default']
                        ).'</span>'.
                        '<span style="margin-right: 5px;">'.
                        Html::a('<i class="glyphicon glyphicon-edit"></i> '.Yii::t('backend', 'Actualizar'),
                            ['#'], ['class' => 'btn btn-update_item_imported btn-default']
                        ).'</span>'.
//                        '<span style="margin-right: 5px;">'.
//                        Html::a('<i class="glyphicon glyphicon-transfer"></i> '.Yii::t('backend', 'Ajustar precio'),
//                            ['#'], ['class' => 'btn btn-change_price_item_imported btn-default']
//                        ).'</span>'.
                        '<span style="margin-right: 5px;">'.
                        Html::a('<i class="glyphicon glyphicon-minus-sign text-danger"></i> '.Yii::t('backend', 'Eliminar'),
                            ['#'], ['class' => 'btn btn-delete_item_imported btn-default']
                        ).'</span>',
                        'after' => '',
                        'showFooter' => false
        ];
      
      
      $columns = [
         ['class' => 'kartik\grid\SerialColumn', 'order' => DynaGrid::ORDER_FIX_LEFT],
            [
                'class' => '\kartik\grid\CheckboxColumn',
                'checkboxOptions' => [
                    'class' => 'simple'
                ],
                //'pageSummary' => true,
                'rowSelectedClass' => GridView::TYPE_SUCCESS,
            ],

			[
				'attribute' => 'code',
				'contentOptions'=>['class'=>'kv-align-left kv-align-middle'], // <-- right here	
				'hAlign'=>'center',
                'group' => true,
			],

			[
				'attribute' => 'name',
                'headerOptions' => ['class'=>'custom_width'],
                'contentOptions' => ['class'=>'custom_width'],
				'hAlign'=>'center',					
			],
            [
              'attribute'=>'quantity',
              'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
              'vAlign' => 'middle',
              'hAlign' => 'center',
              'filterType'=>GridView::FILTER_NUMBER,
              'filterWidgetOptions'=>[
                  'maskedInputOptions' => [
                      'allowMinus' => false,
                      'groupSeparator' => '.',
                      'radixPoint' => ',',
                      'digits' => 0
                  ],
                  'displayOptions' => ['class' => 'form-control kv-monospace'],
                  'saveInputContainer' => ['class' => 'kv-saved-cont']
              ],
              'value' => function ($data) {
                  return GlobalFunctions::formatNumber($data->quantity,0);
              },
              'format' => 'html',
            ],
            [
              'attribute'=>'price_by_unit',
              'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
              'vAlign' => 'middle',
              'hAlign' => 'center',
              'pageSummary' => true,
              'pageSummaryFunc' => GridView::F_SUM,
              'filterType'=>GridView::FILTER_NUMBER,
              'filterWidgetOptions'=>[
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
                  return GlobalFunctions::formatNumber($data->price_by_unit,2);
              },
              'format' => 'html',
            ],
            [
              'attribute'=>'amount_total',
              'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
              'vAlign' => 'middle',
              'hAlign' => 'center',
              'pageSummary' => true,
              'pageSummaryFunc' => GridView::F_SUM,
              'filterType'=>GridView::FILTER_NUMBER,
              'filterWidgetOptions'=>[
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
                  return GlobalFunctions::formatNumber($data->amount_total,2);
              },
              'format' => 'html',
            ],

            [
              'attribute'=>'status',
              'format' => 'raw',
              'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
              'filterType'=>GridView::FILTER_SELECT2,
              'filter'=> ItemImported::getStatusSelectType(),
              'filterWidgetOptions' => [
                  'pluginOptions'=>['allowClear'=>true],
                  'options'=>['multiple'=>false],
              ],
              'value' => function($data) {
                    $btn_extra = '';
                    if($data->status == ItemImported::STATUS_PRODUCT_NOT_FOUND)
                    {
                        $new_product = new Product([
                            'supplier_code' => $data->code,
                            'bar_code' => $data->code,
                            'description' => $data->name,
                            'price' => $data->price_by_unit,
                        ]);

                        $invoice_date = $data->xmlImported->invoice_date;
                        if(isset($invoice_date) && !empty($invoice_date))
                        {
                            $new_product->entry_date = substr($invoice_date,0,10);
                        }

                        $unit_measure = $data->unit_measure_commercial;
                        if(isset($unit_measure) && !empty($unit_measure))
                        {
                            if(UnitType::find()->where(['code' => $unit_measure])->exists())
                            {
                                $new_product->unit_type_id = UnitType::getUnitTypeIdByCode($unit_measure);
                            }
                            else
                            {
                                $new_unit = new UnitType(['code' => $unit_measure, 'name' => $unit_measure,'status' => 1]);
                                if($new_unit->save())
                                {
                                    $new_product->unit_type_id = UnitType::getUnitTypeIdByCode($unit_measure);
                                }
                                else
                                {
                                    $new_product->unit_type_id = UnitType::getUnitTypeIdByCode('Unid');
                                }
                            }
                        }

                        $btn_extra = Html::a('<i class="fa fa-plus"></i> '.Yii::t('backend','Crear'),['product/create','pre_model'=>urlencode(serialize($new_product))],['target'=> '_blank','class'=>'btn btn-flat btn-default btn-xs','title'=>'Crear Producto','data-pjax'=>0,'data' => ['method' => 'post']]);
                    }
                    else if($data->status == ItemImported::STATUS_ALERT_PRICE_DISTINCT)
                    {
                        if(GlobalFunctions::getRol() !== User::ROLE_FACTURADOR)
                        {
                            $btn_extra = Html::a('<i class="glyphicon glyphicon-transfer"></i> '.Yii::t('backend','Ajustar'),['item-imported/change_price_ajax','id'=> $data->id],['class'=>'btn btn-flat btn-default btn-xs btn-change_price_item_imported','title'=>'Ajustar']);
                        }

                    }
                    return ItemImported::getStatusSelectType($data->status,false,true).' '.$btn_extra;
              },
              'filterInputOptions'=>['placeholder'=> '------'],
              'hAlign'=>'center',
            ],


      ];
      ?>
    
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <!-- /.box-header -->
                <div class="box-body">
    <?php 
                    $dynagrid = DynaGrid::begin([			                
                                'columns' => $columns,
                                'theme' => 'panel-default',
                                'showPersonalize' => false,
                                //'storage' => 'db',
                                //'maxPageSize' =>500,
                                'allowSortSetting' => true,
                                'gridOptions' => [
                                    'dataProvider' => $dataProvider,
                                    'filterModel' => $searchModel,
									'emptyCell'=>'',
                                    'id' => 'itemimported',
                                    'autoXlFormat'=>true,
                                    'export'=>[
                                        'fontAwesome'=>true,
                                        'showConfirmAlert'=>false,
                                        'target'=>GridView::TARGET_BLANK
                                    ],
                                    'floatHeader' => false,
                                    'showPageSummary' => false,
                                    'pjax' => true,
                                    'pjaxSettings'=>[
                                        'options'=>[
                                            'enablePushState'=>false,
                                        ],
                                    ],
                                    'panel' => $panels,
                                    'toolbar' => $toolbars,
                                    'hover' => 'true'
                                ],
                                'options' => ['id' => 'grid-item_imported'] // a unique identifier is important
                    ]);
    
                    DynaGrid::end();
    ?> 			</div>
            </div>
        </div>
        <!-- /.box-header -->
    </div>
</div>

<?php
}
?>

<div id="panel-form-item_imported">

</div>
    <input type="hidden" id="entry_id" value="<?= $model->id?>" />

<?php
// Register action buttons js
$this->registerJs('

$(document).ready(function(e) {
	function init_click_handlers_item_entry(){
		
		$("a.btn-update_item_imported").click(function(e) {
			e.preventDefault();
            var selectedId = $("#itemimported").yiiGridView("getSelectedRows");
            if(selectedId.length == 0) {
				bootbox.alert("Seleccione un elemento");                
            } else if(selectedId.length>1){
				bootbox.alert("Solo puede seleccionar un elemento");          				
            } else {
				var url = "'.Url::to(['/item-imported/update_ajax'], GlobalFunctions::URLTYPE).'"; 	
				$.ajax({
					type: "GET",
					url : url,     
					data : {id: selectedId[0]},
					success : function(data) {
						$("#panel-grid-item_imported").hide(500);
						$("#panel-form-item_imported").html(data);
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
        });
		
		$("a.btn-location_item_imported").click(function(e) {
			e.preventDefault();
            var selectedId = $("#itemimported").yiiGridView("getSelectedRows");
            if(selectedId.length == 0) {
				bootbox.alert("Seleccione al menos un elemento");                
            }
             else { 
				var url = "'.Url::to(['/item-imported/assign_multiple_location_ajax', 'branch_office_id' => $model->branch_office_id], GlobalFunctions::URLTYPE).'"; 	
				$.ajax({
					type: "GET",
					url : url,     
					data : {ids: selectedId},
					success : function(data) {
						$("#panel-grid-item_imported").hide(500);
						$("#panel-form-item_imported").html(data);
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
        });
        
        $("a.btn-delete_item_imported").click(function(e) {
			e.preventDefault();
            var selectedId = $("#itemimported").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
				bootbox.alert("Seleccione al menos un elemento"); 
            } else {
				var url = "'.Url::to(['/item-imported/deletemultiple_ajax'], GlobalFunctions::URLTYPE).'";				
				bootbox.confirm({
					message: "¿Est&aacute; seguro que desea eliminar este registro?",
					buttons: {
						confirm: {
							label: "Si",
							className: "btn-success"
						},
						cancel: {
							label: "No",
							className: "btn-danger"
						}
					},
					callback: function (result) {
						 if (result)
						 {
							$.ajax({
								type: "POST",
								url : url,
								data : {ids: selectedId},
								success : function(response) {
									$.pjax.reload({container:"#grid-item_imported-pjax"});
									$.notify({
										"message": response.message,
										"icon": "glyphicon glyphicon-ok-sign",
										"title": response.titulo,										
										"showProgressbar": false,
										"url":"",						
										"target":"_blank"},{"type": response.type}
									);
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
				});				
            }
        });
        
        $("a.btn-aprov_item_imported").click(function(e) {
			e.preventDefault();
            var url = "'.Url::to(['/item-imported/aprovmultiple_ajax'], GlobalFunctions::URLTYPE).'";				
            bootbox.confirm({
                message: "¿Est&aacute; seguro que desea aprobar los items listos?",
                buttons: {
                    confirm: {
                        label: "Si",
                        className: "btn-success"
                    },
                    cancel: {
                        label: "No",
                        className: "btn-danger"
                    }
                },
                callback: function (result) {
                     if (result)
                     {
                        $.ajax({
                            type: "POST",
                            url : url,
                            success : function(response) {
                                $.pjax.reload({container:"#grid-item_imported-pjax"}).done(function(){
                                    $.pjax.reload({container:"#grid-item_entry-pjax"});
                                });
                                
                                $.notify({
                                    "message": response.message,
                                    "icon": "glyphicon glyphicon-ok-sign",
                                    "title": response.titulo,										
                                    "showProgressbar": false,
                                    "url":"",						
                                    "target":"_blank"},{"type": response.type}
                                );
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
            });				
        });
        
        $("a.btn-refresh_item_imported").click(function(e) {
			e.preventDefault();
            $.pjax.reload({container:"#grid-item_imported-pjax"});
        });
        
        $("a.btn-change_price_item_imported").click(function(e) {
			e.preventDefault();          
            var url = e.target.href; 	
            
            $.ajax({
                type: "POST",
                url : url,     
                success : function(data) {
                    $("#panel-grid-item_imported").hide(500);
                    $("#panel-form-item_imported").html(data);
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
            
        });
	}

	init_click_handlers_item_entry(); //first run
	$("#grid-item_imported-pjax").on("pjax:success", function() {
	    init_click_handlers_item_entry(); //reactivate links in grid after pjax update
	});
});
');
?>