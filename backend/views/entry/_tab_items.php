<?php
use common\models\GlobalFunctions;
use kartik\dynagrid\DynaGrid;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $searchModel \backend\models\business\ItemEntrySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$title = Yii::t('backend', 'Items');
?>
<?php
if ($model->isNewRecord) {
    $label_update = Yii::t('backend', 'Para adicionar items, debe crear antes la entrada');

    echo "<br /><div class=\"alert-warning alert fade in\">
			<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>
			<i class=\"icon fa fa-warning\"></i>$label_update
			</div>";
}
else
{
?>
    <div class="item-entry-index" style="margin-top:15px;" id="panel-grid-item_entry">
      <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
      
    <?php 
         $toolbars = [
            ['content' => '{dynagrid}'],
            //'{export}',
            '{toggleData}',
        ];
        
        $panels = [
            'heading'    => '<h3 class="panel-title"><i class="fa fa-file-text-o"></i></h3>',
                    'type' => 'default',
                    'before' => '<span style="margin-right: 5px;">'.
                        Html::a('<i class="glyphicon glyphicon-plus text-primary"></i> '.Yii::t('backend', 'Crear'),
                            ['#'], ['class' => 'btn btn-create-item_entry btn-default']
                        ).'</span>',
                        'after' => '',
                        'showFooter' => false    
        ];
      
      
      $columns = [
         ['class' => 'kartik\grid\SerialColumn', 'order' => DynaGrid::ORDER_FIX_LEFT],

			[
				'attribute' => 'product_code',
				'contentOptions'=>['class'=>'kv-align-left kv-align-middle'], // <-- right here	
				'hAlign'=>'center',					
			],

            [
				'attribute' => 'product_description',
				'contentOptions'=>['class'=>'kv-align-left kv-align-middle'], // <-- right here
				'hAlign'=>'center',
			],

			[
				'attribute' => 'price',
				'contentOptions'=>['class'=>'kv-align-left kv-align-middle'], // <-- right here	
				'hAlign'=>'center',		
				'format'=>['decimal', 2],			
			],
			[
				'attribute' => 'tax_amount',
				'contentOptions'=>['class'=>'kv-align-left kv-align-middle'], // <-- right here	
				'hAlign'=>'center',	
				'format'=>['decimal', 2],				
			],

			[
				'attribute' => 'subtotal',
				'contentOptions'=>['class'=>'kv-align-left kv-align-middle'], // <-- right here	
				'hAlign'=>'center',		
				'format'=>['decimal', 2],			
			],

			[
				'attribute' => 'entry_quantity',
				'contentOptions'=>['class'=>'kv-align-left kv-align-middle'], // <-- right here	
				'hAlign'=>'center',					
			],

			[
				'attribute' => 'past_quantity',
				'contentOptions'=>['class'=>'kv-align-left kv-align-middle'], // <-- right here	
				'hAlign'=>'center',					
			],

			[
				'attribute' => 'past_price',
				'contentOptions'=>['class'=>'kv-align-left kv-align-middle'], // <-- right here	
				'hAlign'=>'center',					
			],

            [
				'attribute' => 'new_quantity',
				'contentOptions'=>['class'=>'kv-align-left kv-align-middle'], // <-- right here
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
                                    'id' => 'contact',
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
                                'options' => ['id' => 'grid-item_entry'] // a unique identifier is important
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

<div id="panel-form-item_entry">

</div>
    <input type="hidden" id="entry_id" value="<?= $model->id?>" />

<?php
// Register action buttons js
$this->registerJs('

$(document).ready(function(e) {
	function init_click_handlers_item_entry(){
		
		$("a.btn-create-item_entry").click(function(e) {
			e.preventDefault();
			$("#panel-grid-item_entry").hide();
			var url = "'.Url::to(['/item-entry/create_ajax'], GlobalFunctions::URLTYPE).'?id="+$("#entry_id").val(); 	
			$.ajax({
				type: "GET",
				url : url,     
				success : function(data) {
					$("#panel-form-item_entry").html(data);
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
		
		$("a.btn-update-item_entry").click(function(e) {
			e.preventDefault();
            var selectedId = $("#contact").yiiGridView("getSelectedRows");
            if(selectedId.length == 0) {
				bootbox.alert("Seleccione un elemento");                
            } else if(selectedId.length>1){
				bootbox.alert("Solo puede seleccionar un elemento");          				
            } else {
				var url = "'.Url::to(['/item-entry/update_ajax'], GlobalFunctions::URLTYPE).'"; 	
				$.ajax({
					type: "GET",
					url : url,     
					data : {id: selectedId[0]},
					success : function(data) {
						$("#panel-grid-item_entry").hide(500);
						$("#panel-form-item_entry").html(data);
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
		
        $("a.btn-delete-item_entry").click(function(e) {
			e.preventDefault();
            var selectedId = $("#contact").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
				bootbox.alert("Seleccione al menos un elemento"); 
            } else {
				var url = "'.Url::to(['/item-entry/deletemultiple_ajax'], GlobalFunctions::URLTYPE).'";				
				bootbox.confirm({
					message: "Est&aacute; seguro que desea eliminar este registro?",
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
									$.pjax.reload({container:"#grid-item_entry-pjax"});
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
	}

	init_click_handlers_item_entry(); //first run
	$("#grid-item_entry-pjax").on("pjax:success", function() {
	    init_click_handlers_item_entry(); //reactivate links in grid after pjax update
	});
});
');
?>