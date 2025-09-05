<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\dynagrid\DynaGrid;
use yii\helpers\Url;
use backend\models\business\ItemDebitNoteForm;
use common\models\GlobalFunctions;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\UtilsConstants;
use mdm\admin\components\Helper;

/* @var $this yii\web\View */
/* @var $searchModelItems \backend\models\business\ItemDebitNoteSearch */
/* @var $dataProviderItems yii\data\ActiveDataProvider */

$title = Yii::t('backend', 'Items');
?>
<?php
if ($model->isNewRecord) {
    $label_update = Yii::t('backend', 'Para adicionar items, debe crear antes la nota de débito');

    echo "<br /><div class=\"alert-warning alert fade in\">
			<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>
			<i class=\"icon fa fa-warning\"></i>$label_update
			</div>";
}
else
{
?>
    <?= GlobalFunctions::showModalHtmlContent(Yii::t('backend','Imagen'),'modal-lg') ?>

    <div class="item-debit-note-index" style="margin-top:15px;" id="panel-grid-item_debitnote">
    <?php
        echo $this->render('_form-item_search', ['model' => new ItemDebitNoteForm(['quantity' => '','debit_note_id' => $model->id])]);

        $toolbars = [
        //['content' => '{dynagrid}'],
        [
            'content' =>
                Html::a('<i class="fa fa-refresh"></i> '.Yii::t('backend','Resetear'), ['index'], ['class' => 'btn btn-default btn-flat btn-refresh_item_invoice','title'=> Yii::t('backend','Resetear listado'), 'data-toggle' => 'tooltip', 'data-pjax' => 1]),
        ],
        //'{export}',
        '{toggleData}',
    ];

    $panels = [
        'heading'    => '<h3 class="panel-title"><i class="fa fa-file-text-o"></i></h3>',
        'type' => 'default',
        'before' =>
            '<span style="margin-right: 5px;">'.
            Html::a('<i class="glyphicon glyphicon-minus-sign text-danger"></i> '.Yii::t('backend', 'Eliminar'),
                ['#'], ['class' => 'btn btn-delete_item_invoice btn-default']
            ).'</span>'
        ,
        'after' => "<table cellpadding=\"10\" cellspacing=\"10\" align=\"right\" style=\"margin-top:20px\">
									<tr>
										<td width=\"150px\" class='text-right'>
											<strong>Subtotal</strong>
										</td>
										<td align=\"right\" width=\"120px\">
											&nbsp;&nbsp;<span id=\"total_subtotal\"></span>
										</td>
									</tr>																												
								    <tr>
										<td class='text-right'>
											<strong>Descuento</strong>
										</td>
										<td align=\"right\">
											&nbsp;&nbsp;<span id=\"total_discount\"></span>
										</td>
									</tr>				
									<tr>
										<td class='text-right'>
											<strong>IVA</strong>
										</td>
										<td align=\"right\">
											&nbsp;&nbsp;<span id=\"total_tax\"></span>
										</td>
									</tr>																						
									<tr>
										<td class='text-right'>
											<strong>Exoneración</strong>
										</td>
										<td align=\"right\">
											&nbsp;&nbsp;<span id=\"total_exonerate\"></span>
										</td>
									</tr>																						
									<tr>
										<td class='text-right'>
											<strong>Total</strong>
										</td>
										<td align=\"right\">
											&nbsp;&nbsp;<span id=\"total_price\"></span>
										</td>
									</tr>																						
									
								</table>",
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
                'hAlign'=>'center'
            ],

            [
                'attribute'=>'description',
                'headerOptions' => ['class'=>'custom_width'],
                'contentOptions' => ['class'=>'custom_width'],
                'hAlign'=>'center',
                'format'=> 'raw',
                'value' => function ($data)
                {
                  return $data->description;
                }
            ],

            [
              'attribute'=>'quantity',
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
              'attribute'=>'unit_type_id',
              'format' => 'html',
              'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
              'filterType'=>GridView::FILTER_SELECT2,
              'filter'=> UnitType::getSelectMap(true,true),
              'filterWidgetOptions' => [
                  'pluginOptions'=>['allowClear'=>true],
                  'options'=>['multiple'=>false],
              ],
              'value'=> function($model){
                  if(isset($model->product))
                  {
                      return $model->unitType->code;
                  }
                  else
                  {
                      return 'N/A';
                  }
              },
              'filterInputOptions'=>['placeholder'=> '------'],
              'hAlign'=>'center',
            ],

            [
              'attribute'=>'price_type',
              'format' => 'html',
              'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
              'filterType'=>GridView::FILTER_SELECT2,
              'filter'=> UtilsConstants::getCustomerAsssignPriceSelectType(),
              'filterWidgetOptions' => [
                  'pluginOptions'=>['allowClear'=>true],
                  'options'=>['multiple'=>false],
              ],
              'value'=> function($model){
                  return UtilsConstants::getCustomerAsssignPriceSelectType($model->price_type);
              },
              'filterInputOptions'=>['placeholder'=> '------'],
              'hAlign'=>'center',
            ],

            [
              'attribute'=>'price_unit',
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
                  return GlobalFunctions::formatNumber($data->price_unit,2);
              },
              'format' => 'html',
            ],

            [
              'attribute'=>'subtotal',
              'label' => Yii::t('backend','Importe'),
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
                  return GlobalFunctions::formatNumber($data->subtotal,2);
              },
              'format' => 'html',
            ],

          [
              'class' => 'kartik\grid\ActionColumn',
              'dropdown' => false,
              'vAlign'=>'middle',
              'dropdownOptions' => ['class' => 'float-right'],
              'headerOptions' => ['class' => 'kartik-sheet-style'],
              'template' => Helper::filterActionColumn(['image', 'update', 'delete']),
              'buttons' => [
                  'image' => function($url, $model) {

                      if(isset($model->product_id))
                      {
                          $image = '<i class="fa fa-image modalClickImage" data-href="'. $model->product->getPreview().'"></i>';
                      }
                      else
                      {
                          $image = '';
                      }
                      return $image;
                  },
                  'update' => function($url, $model) {
                      $options = [
                          'title' => Yii::t('backend', 'Actualizar'),
                          'class' => 'btn btn-xs btn-default btn-flat btn-update_item_debitnote',
                          'aria-label' => Yii::t('backend', 'Actualizar'),
                          'data-pjax' => '0',
                          'data-toggle' => 'tooltip',
                      ];
                      return Html::a('<i class="glyphicon glyphicon-pencil"></i>', Url::to(['/item-debit-note/update_ajax','id'=>$model->id], GlobalFunctions::URLTYPE), $options);
                  },
                  'delete' => function($url, $model) {
                    $options = [
                        'title' => Yii::t('backend', 'Eliminar'),
                        'class' => 'btn btn-xs btn-danger btn-flat btn-simple_delete_item_invoice',
                        'aria-label' => Yii::t('backend', 'Eliminar'),
                        'data-pjax' => '0',
                        'data-toggle' => 'tooltip',
                    ];
                    return Html::a('<i class="glyphicon glyphicon-trash"></i>', Url::to(['/item-debit-note/delete_ajax','id'=>$model->id], GlobalFunctions::URLTYPE), $options);
                }
              ],
          ]

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
                                    'dataProvider' => $dataProviderItems,
                                    'filterModel' => $searchModelItems,
									'emptyCell'=>'',
                                    'id' => 'itemdebitnote',
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
                                ],
                                'options' => ['id' => 'grid_debitnote'] // a unique identifier is important
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

<div id="panel-form-item_debitnote">

</div>
    <input type="hidden" id="credit_note_id" value="<?= $model->id?>" />

<?php
// Register action buttons js
$this->registerJs('
$(document).ready(function(e) {
	function init_click_handlers_item_invoice(){
	
	$("a.btn-update_item_debitnote").click(function(e) {
			e.preventDefault();
				$.ajax({
					type: "GET",
					url : $(this).attr("href"),
					success : function(data) {
						$("#panel-grid-item_debitnote").hide(500);
						$("#panel-form-item_debitnote").html(data);
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
        
        $("a.btn-simple_delete_item_invoice").click(function(e) {
			e.preventDefault();
			var url = $(this).attr("href");
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
								success : function(response) {
									$.pjax.reload({container:"#grid_debitnote-pjax"});
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
               
        $("a.btn-delete_item_invoice").click(function(e) {
			e.preventDefault();
            var selectedId = $("#itemdebitnote").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
				bootbox.alert("Seleccione al menos un elemento"); 
            } else {
				var url = "'.Url::to(['/item-debit-note/deletemultiple_ajax'], GlobalFunctions::URLTYPE).'";				
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
									$.pjax.reload({container:"#grid_debitnote-pjax"});
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
	
		$("a.btn-refresh_item_invoice").click(function(e) {
			e.preventDefault();
            $.pjax.reload({container:"#grid_debitnote-pjax"});
        });
        
        function updateValores()
		{
			var fID = "'.$model->id.'";
			if(fID != "")
			{
                $.ajax({
                    type: \'POST\',			
                    dataType: "json",
                    url : "'.Url::to(['/debit-note/get-resume-debit-note?id='.$model->id], GlobalFunctions::URLTYPE).'",
                    data : {id: fID},
                    success : function(json) {			
                        $("#total_subtotal").html(json.total_subtotal);					
                        $("#total_tax").html(json.total_tax);					
                        $("#total_discount").html(json.total_discount);					
                        $("#total_exonerate").html(json.total_exonerate);
                        $("#total_price").html(json.total_price);				
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
	
		updateValores();	
        
	}
	
	function appendClickImage(){
            $(\'.modalClickImage\').click(function (e) {
                e.preventDefault();
                var img = \'<div class="text-center img-bordered"><img style="width: 100%;" src="\'+$(this).attr(\'data-href\')+\'"></div>\';
                $(\'#modal\').modal(\'show\').find(\'#modalContent\').html(img);
            });
        }

	init_click_handlers_item_invoice(); //first run
	appendClickImage();
	$("#grid_debitnote-pjax").on("pjax:success", function() {
	    init_click_handlers_item_invoice(); //reactivate links in grid after pjax update
	    appendClickImage();
	});
});
');
?>