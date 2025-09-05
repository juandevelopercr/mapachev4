<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\dynagrid\DynaGrid;
use common\models\User;
use yii\helpers\Url;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use backend\models\business\SectorLocation;
use common\models\GlobalFunctions;

/* @var $this yii\web\View */
/* @var $searchModel \backend\models\business\SupplierContactSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$title = Yii::t('backend', 'Ubicaciones físicas');
?>
<?php
if ($model->isNewRecord) {
    $label_update = Yii::t('backend', 'Para adicionar ubicaciones, debe crear antes el producto');

    echo "<br /><div class=\"alert-warning alert fade in\">
			<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>
			<i class=\"icon fa fa-warning\"></i>$label_update
			</div>";
}
else
{
?>
    <div class="physical_locations-index" style="margin-top:15px;" id="panel-grid-physical_location">
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
                            ['#'], ['class' => 'btn btn-create-physical_location btn-default']
                        ).'</span><span style="margin-right: 5px;">',
                        'after' => '',
                        'showFooter' => false    
        ];
      
      
      $columns = [
         ['class' => 'kartik\grid\SerialColumn', 'order' => DynaGrid::ORDER_FIX_LEFT],

              [
                  'attribute'=>'sector_location_id',
                  'format' => 'html',
                  'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                  'filterType'=>GridView::FILTER_SELECT2,
                  'filter'=> SectorLocation::getSelectMap(),
                  'filterWidgetOptions' => [
                      'pluginOptions'=>['allowClear'=>true],
                      'options'=>['multiple'=>false],
                  ],
                  'value'=> function($data){
                        return $data->sectorLocation->code.' - '.$data->sectorLocation->name .' ['.$data->sectorLocation->sector->code.' - '.$data->sectorLocation->sector->name.']'.' ('.$data->sectorLocation->sector->branchOffice->code.' - '.$data->sectorLocation->sector->branchOffice->name.')';
                  },
                  'filterInputOptions'=>['placeholder'=> '------'],
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
                          'digits' => 5
                      ],
                      'displayOptions' => ['class' => 'form-control kv-monospace'],
                      'saveInputContainer' => ['class' => 'kv-saved-cont']
                  ],
                  'value' => function ($data) {
                      return GlobalFunctions::formatNumber($data->quantity,2);
                  },
                  'format' => 'html',
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
									'emptyCell'=>'',
                                    'id' => 'physical_location',
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
                                'options' => ['id' => 'grid-physical_location'] // a unique identifier is important
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

<div id="panel-form-physical_location">

</div>
<input type="hidden" id="product_id" value="<?= $model->id?>" />

<?php
// Register action buttons js
$this->registerJs('

$(document).ready(function(e) {
	function init_click_handlers_physical_location(){
		
		$("a.btn-create-physical_location").click(function(e) {
			e.preventDefault();
			$("#panel-grid-physical_location").hide();
			var url = "'.Url::to(['/physical-location/create_ajax'], GlobalFunctions::URLTYPE).'?id="+$("#product_id").val(); 	
			$.ajax({
				type: "GET",
				url : url,     
				success : function(data) {
					$("#panel-form-physical_location").html(data);
					if(data.type == \'danger\')
                    {                
                        $.each(data.errors, function(key, val) {
                            $("#physicallocation-"+key).after("<div class=\"help-block\">"+val+"</div>");
                            $("#physicallocation-"+key).closest(".form-group").addClass("has-error");
                        });
                    }
                  
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					$.notify({
						"message": "Ha ocurrido un error. Inténtelo nuevamente, si el error persiste, póngase en physical_locationo con el administrador del sistema",
						"icon": "glyphicon glyphicon-remove text-danger-sign",
						"title": "Informaci&oacute;n <hr class=\"kv-alert-separator\">",						
						"showProgressbar": false,
						"url":"",						
						"target":"_blank"},{"type": "danger"}
					);
				}				
			});				
        });
	
	}

	init_click_handlers_physical_location(); //first run
	$("#grid-physical_location-pjax").on("pjax:success", function() {
	    init_click_handlers_physical_location(); //reactivate links in grid after pjax update
	});
});
');
?>