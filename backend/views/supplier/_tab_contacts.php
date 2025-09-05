<?php
use backend\models\nomenclators\Department;
use backend\models\nomenclators\JobPosition;
use common\models\GlobalFunctions;
use common\models\User;
use kartik\dynagrid\DynaGrid;
use kartik\grid\GridView;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $searchModel \backend\models\business\SupplierContactSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$title = Yii::t('app', 'Contactos');
?>
<?php
if ($model->isNewRecord) {
    $label_update = Yii::t('backend', 'Para adicionar contactos, debe crear antes el proveedor');

    echo "<br /><div class=\"alert-warning alert fade in\">
			<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>
			<i class=\"icon fa fa-warning\"></i>$label_update
			</div>";
}
else
{
?>
    <div class="contacts-index" style="margin-top:15px;" id="panel-grid-contact">
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
                            ['#'], ['class' => 'btn btn-create-contact btn-default']
                        ).'</span><span style="margin-right: 5px;">'.
                        Html::a('<i class="glyphicon glyphicon-edit"></i> '.Yii::t('backend', 'Actualizar'),
                            '#', ['class' => 'btn btn-update-contact btn-default']
                        ).'</span><span style="margin-right: 5px;">'.
                        Html::a('<i class="glyphicon glyphicon-minus-sign text-danger"></i> '.Yii::t('backend', 'Eliminar'),
                            '#', ['class' => 'btn btn-delete-contact btn-default']
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
				'attribute' => 'name',
				'contentOptions'=>['class'=>'kv-align-left kv-align-middle'], // <-- right here	
				'hAlign'=>'center',					
			],				
			[
				'attribute' => 'email',
				'contentOptions'=>['class'=>'kv-align-left kv-align-middle'], // <-- right here	
				'hAlign'=>'center',					
			],				
			[
				'attribute' => 'phone',
				'contentOptions'=>['class'=>'kv-align-left kv-align-middle'], // <-- right here	
				'hAlign'=>'center',					
			],				
			[
				'attribute' => 'ext',
				'contentOptions'=>['class'=>'kv-align-left kv-align-middle'], // <-- right here	
				'hAlign'=>'center',					
			],				
			[
				'attribute' => 'cellphone',
				'contentOptions'=>['class'=>'kv-align-left kv-align-middle'], // <-- right here	
				'hAlign'=>'center',					
			],
          [
              'attribute'=>'department_id',
              'format' => 'html',
              'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
              'filterType'=>GridView::FILTER_SELECT2,
              'filter'=> Department::getSelectMap(),
              'filterWidgetOptions' => [
                  'pluginOptions'=>['allowClear'=>true],
                  'options'=>['multiple'=>false],
              ],
              'value'=> 'department.name',
              'filterInputOptions'=>['placeholder'=> '------'],
              'hAlign'=>'center',
          ],

          [
              'attribute'=>'job_position_id',
              'format' => 'html',
              'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
              'filterType'=>GridView::FILTER_SELECT2,
              'filter'=> JobPosition::getSelectMap(),
              'filterWidgetOptions' => [
                  'pluginOptions'=>['allowClear'=>true],
                  'options'=>['multiple'=>false],
              ],
              'value'=> 'jobPosition.name',
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
                                'options' => ['id' => 'grid-contact'] // a unique identifier is important
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

<div id="panel-form-contact">

</div>
<input type="hidden" id="supplier_id" value="<?= $model->id?>" />

<?php
// Register action buttons js
$this->registerJs('

$(document).ready(function(e) {
	function init_click_handlers_contact(){
		
		$("a.btn-create-contact").click(function(e) {
			e.preventDefault();
			$("#panel-grid-contact").hide();
			var url = "'.Url::to(['/supplier-contact/create_ajax'], GlobalFunctions::URLTYPE).'?id="+$("#supplier_id").val(); 	
			$.ajax({
				type: "GET",
				url : url,     
				success : function(data) {
					$("#panel-form-contact").html(data);
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
		
		$("a.btn-update-contact").click(function(e) {
			e.preventDefault();
            var selectedId = $("#contact").yiiGridView("getSelectedRows");
            if(selectedId.length == 0) {
				bootbox.alert("Seleccione un elemento");                
            } else if(selectedId.length>1){
				bootbox.alert("Solo puede seleccionar un elemento");          				
            } else {
				var url = "'.Url::to(['/supplier-contact/update_ajax'], GlobalFunctions::URLTYPE).'"; 	
				$.ajax({
					type: "GET",
					url : url,     
					data : {id: selectedId[0]},
					success : function(data) {
						$("#panel-grid-contact").hide(500);
						$("#panel-form-contact").html(data);
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
		
        $("a.btn-delete-contact").click(function(e) {
			e.preventDefault();
            var selectedId = $("#contact").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
				bootbox.alert("Seleccione al menos un elemento"); 
            } else {
				var url = "'.Url::to(['/supplier-contact/deletemultiple_ajax'], GlobalFunctions::URLTYPE).'";				
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
									$.pjax.reload({container:"#grid-contact-pjax"});
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

	init_click_handlers_contact(); //first run
	$("#grid-contact-pjax").on("pjax:success", function() {
	    init_click_handlers_contact(); //reactivate links in grid after pjax update
	});
});
');
?>