<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\dynagrid\DynaGrid;
use yii\helpers\Url;
use backend\models\business\ItemPaymetOrderForm;
use common\models\GlobalFunctions;
use kartik\number\NumberControl;
use backend\models\nomenclators\UnitType;

/* @var $this yii\web\View */
/* @var $searchModel \backend\models\business\ReceptionItemPoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$title = Yii::t('backend', 'Adjuntos de orden de compra');
?>

<?php
if ($model->isNewRecord) {
    $label_update = Yii::t('backend', 'Para gestionar los adjuntos, debe crear antes la orden de compra');

    echo "<br /><div class=\"alert-warning alert fade in\">
			<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>
			<i class=\"icon fa fa-warning\"></i>$label_update
			</div>";
}
else
{
?>
    <div class="attach-po-index" style="margin-top:15px;" id="panel-grid-attach_po">

    <?php
    $toolbars = [
        //['content' => '{dynagrid}'],
        [
            'content' =>
                Html::a('<i class="fa fa-refresh"></i> '.Yii::t('backend','Resetear'), ['index'], ['class' => 'btn btn-default btn-flat btn-refresh_attach_po','title'=> Yii::t('backend','Resetear listado'), 'data-toggle' => 'tooltip', 'data-pjax' => 1]),
        ],
        //'{export}',
        '{toggleData}',
    ];

    $panels = [
        'heading'    => '<h3 class="panel-title"><i class="fa fa-file-text-o"></i></h3>',
        'type' => 'default',
        'before' =>
            '<span style="margin-right: 5px;">'.
            Html::a('<i class="glyphicon glyphicon-plus text-primary"></i> '.Yii::t('backend', 'Crear'),
                ['#'], ['class' => 'btn btn-create-attach_po btn-default']
            ).'</span>'.
            '<span style="margin-right: 5px;">'.
            Html::a('<i class="glyphicon glyphicon-edit"></i> '.Yii::t('backend', 'Actualizar'),
                ['#'], ['class' => 'btn btn-update_attach_po btn-default']
            ).'</span>'.
            '<span style="margin-right: 5px;">'.
            Html::a('<i class="glyphicon glyphicon-minus-sign text-danger"></i> '.Yii::t('backend', 'Eliminar'),
                ['#'], ['class' => 'btn btn-delete_attach_po btn-default']
            ).'</span>'
        ,
        'after' => "",
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
              'attribute'=>'document_file',
              'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
              'hAlign'=>'center',
              'format'=> 'raw',
              'value' => function ($data) {
                  $img = '<img class="preview-index img-bordered" src="'. $data->getPreview() .'">';
                  return Html::a($img,['/'.$data->getImageUrl()],['data-pjax' => 0,'target' => '_blank']);
              },
              'filter' => false
          ],

          [
              'attribute' => 'observations',
              'headerOptions' => ['class'=>'custom_width'],
              'contentOptions' => ['class'=>'custom_width'],
              'hAlign'=>'center',
              'format'=> 'html',
              'value' => function ($data) {
                  return $data->observations;
              }
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
                                    'id' => 'attach_po',
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
                                'options' => ['id' => 'grid-attach_po'] // a unique identifier is important
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

<div id="panel-form-attach_po">

</div>
    <input type="hidden" id="payment_order_id" value="<?= $model->id?>" />


<?php
// Register action buttons js
$this->registerJs('

$(document).ready(function(e) {
                    
	function init_click_handlers_attach_po(){
        
		$("a.btn-create-attach_po").click(function(e) {
			e.preventDefault();
			$("#panel-grid-attach_po").hide();
			var url = "'.Url::to(['/attach-po/create_ajax'], GlobalFunctions::URLTYPE).'?id="+$("#payment_order_id").val(); 	
			$.ajax({
				type: "GET",
				url : url,     
				success : function(data) {
					$("#panel-form-attach_po").html(data);
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					$.notify({
						"message": "Ha ocurrido un error. Inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema",
						"icon": "glyphicon glyphicon-remove text-danger-sign",
						"title": "Informaci&oacute;n <hr class=\"kv-alert-separator\">",						
						"showProgressbar": false,
						"url":"",						
						"target":"_blank"},{"type": "danger"}
					);
				}				
			});				
        });
		$("a.btn-update_attach_po").click(function(e) {
			e.preventDefault();
            var selectedId = $("#attach_po").yiiGridView("getSelectedRows");
            if(selectedId.length == 0) {
				bootbox.alert("Seleccione un elemento");                
            } else if(selectedId.length>1){
				bootbox.alert("Solo puede seleccionar un elemento");          				
            } else {
				var url = "'.Url::to(['/attach-po/update_ajax'], GlobalFunctions::URLTYPE).'"; 	
				$.ajax({
					type: "GET",
					url : url,     
					data : {id: selectedId[0]},
					success : function(data) {
						$("#panel-grid-attach_po").hide(500);
						$("#panel-form-attach_po").html(data);
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
        
        $("a.btn-delete_attach_po").click(function(e) {
			e.preventDefault();
            var selectedId = $("#attach_po").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
				bootbox.alert("Seleccione al menos un elemento"); 
            } else {
				var url = "'.Url::to(['/attach-po/deletemultiple_ajax'], GlobalFunctions::URLTYPE).'";				
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
									$.pjax.reload({container:"#grid-attach_po-pjax"});
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
		
		        
        $("a.btn-refresh_attach_po").click(function(e) {
			e.preventDefault();
            $.pjax.reload({container:"#grid-attach_po-pjax"});
        });
          
	}

	init_click_handlers_attach_po(); //first run
	$("#grid-attach_po-pjax").on("pjax:success", function() {
	    init_click_handlers_attach_po(); //reactivate links in grid after pjax update
	});
});
');
?>