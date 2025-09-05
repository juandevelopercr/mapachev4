<?php
use common\models\GlobalFunctions;
use common\models\User;
use kartik\dynagrid\DynaGrid;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $searchModel \backend\models\business\SupplierBankInformationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$title = Yii::t('backend', 'Información Bancaria');
?>
<?php
if ($model->isNewRecord) {
    $label_update = Yii::t('backend', 'Para adicionar información bancaria, debe crear antes el proveedor');

    echo "<br /><div class=\"alert-warning alert fade in\">
			<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>
			<i class=\"icon fa fa-warning\"></i>$label_update
			</div>";
}
else
{
?>
    <div class="bank-index" style="margin-top:15px;" id="panel-grid-bank">

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
                            ['#'], ['class' => 'btn btn-create-bank btn-default']
                        ).'</span><span style="margin-right: 5px;">'.
                        Html::a('<i class="glyphicon glyphicon-edit"></i> '.Yii::t('backend', 'Actualizar'),
                            '#', ['class' => 'btn btn-update-bank btn-default']
                        ).'</span><span style="margin-right: 5px;">'.
                        Html::a('<i class="glyphicon glyphicon-minus-sign text-danger"></i> '.Yii::t('backend', 'Eliminar'),
                            '#', ['class' => 'btn btn-delete-bank btn-default']
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
				'attribute' => 'banck_name',
				'contentOptions'=>['class'=>'kv-align-left kv-align-middle'], // <-- right here	
				'hAlign'=>'center',			
			],
			[
				'attribute' => 'checking_account',
				'contentOptions'=>['class'=>'kv-align-right kv-align-middle'], // <-- right here	
				'hAlign'=>'center',			
			],
			[
				'attribute' => 'customer_account',
				'contentOptions'=>['class'=>'kv-align-right kv-align-middle'], // <-- right here	
				'hAlign'=>'center',
			],
            [
				'attribute' => 'mobile_account',
				'contentOptions'=>['class'=>'kv-align-right kv-align-middle'], // <-- right here
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
                                    'id' => 'bank',
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
                                'options' => ['id' => 'grid-bank'] // a unique identifier is important
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

<div id="panel-form-bank">

</div>
<input type="hidden" id="supplier_id" value="<?= $model->id?>" />

<?php
// Register action buttons js
$this->registerJs('

$(document).ready(function(e) {
	function init_click_handlers_bank(){
		
		$("a.btn-create-bank").click(function(e) {
			e.preventDefault();
			$("#panel-grid-bank").hide();
			var url = "'.Url::to(['/supplier-bank-information/create_ajax'], GlobalFunctions::URLTYPE).'?id="+$("#supplier_id").val(); 	
			$.ajax({
				type: "GET",
				url : url,     
				success : function(data) {
					$("#panel-form-bank").html(data);
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
		
		$("a.btn-update-bank").click(function(e) {
			e.preventDefault();
            var selectedId = $("#bank").yiiGridView("getSelectedRows");
            if(selectedId.length == 0) {
				bootbox.alert("Seleccione un elemento");                
            } else if(selectedId.length>1){
				bootbox.alert("Solo puede seleccionar un elemento");          				
            } else {
				var url = "'.Url::to(['/supplier-bank-information/update_ajax'], GlobalFunctions::URLTYPE).'"; 	
				$.ajax({
					type: "GET",
					url : url,     
					data : {id: selectedId[0]},
					success : function(data) {
						$("#panel-grid-bank").hide(500);
						$("#panel-form-bank").html(data);
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
		
        $("a.btn-delete-bank").click(function(e) {
			e.preventDefault();
            var selectedId = $("#bank").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
				bootbox.alert("Seleccione al menos un elemento"); 
            } else {
				var url = "'.Url::to(['/supplier-bank-information/deletemultiple_ajax'], GlobalFunctions::URLTYPE).'";				
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
									$.pjax.reload({container:"#grid-bank-pjax"});
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

	init_click_handlers_bank(); //first run
	$("#grid-bank-pjax").on("pjax:success", function() {
	    init_click_handlers_bank(); //reactivate links in grid after pjax update
	});
});
');
?>