<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\dynagrid\DynaGrid;
use yii\helpers\Url;
use common\models\GlobalFunctions;
use kartik\editable\Editable;
use mdm\admin\components\Helper;

/* @var $this yii\web\View */
/* @var $model \backend\models\business\PaymentOrder */
/* @var $searchModel \backend\models\business\ReceptionItemPoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$title = Yii::t('backend', 'Recepción de items de compra');
?>
<?php
if ($model->isNewRecord) {
    $label_update = Yii::t('backend', 'Para gestionar la recepción de items, debe crear antes la orden de compra');

    echo "<br /><div class=\"alert-warning alert fade in\">
			<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>
			<i class=\"icon fa fa-warning\"></i>$label_update
			</div>";
} else {
    ?>
    <div class="recepcion-item-po-index" style="margin-top:15px;" id="panel-grid-reception_item_po">

        <?php
        $toolbars = [
            //['content' => '{dynagrid}'],
            [
                'content' =>
                    Html::a('<i class="fa fa-refresh"></i> ' . Yii::t('backend', 'Resetear'), ['index'], ['class' => 'btn btn-default btn-flat btn-refresh_reception_item_po', 'title' => Yii::t('backend', 'Resetear listado'), 'data-toggle' => 'tooltip', 'data-pjax' => 1]),
            ],
            //'{export}',
            '{toggleData}',
        ];

        $panels = [
            'heading' => '<h3 class="panel-title"><i class="fa fa-file-text-o"></i></h3>',
            'type' => 'default',
            'before' =>
                '<span style="margin-right: 5px;">' .
                Html::a('<i class="glyphicon glyphicon-print"></i> ' . Yii::t('backend', 'Reporte Recepción'),
                    ['report', 'id' => $model->id], ['class' => 'btn btn-print_reception_item_po btn-default', 'data-pjax' => 0, 'target' => '_blank']
                ) . '</span>',
            'after' => "",
            'showFooter' => false
        ];


        $columns = [
            ['class' => 'kartik\grid\SerialColumn', 'order' => DynaGrid::ORDER_FIX_LEFT],

            [
                'attribute' => 'bar_code',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'hAlign' => 'center',
                'value' => function ($data) {
                    return $data->itemPaymentOrder->product->bar_code;
                }
            ],

            [
                'attribute' => 'supplier_code',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'], // <-- right here
                'hAlign' => 'center',
                'value' => function ($data) {
                    return $data->itemPaymentOrder->product->supplier_code;
                }
            ],

            [
                'attribute' => 'description',
                'headerOptions' => ['class' => 'custom_width'],
                'contentOptions' => ['class' => 'custom_width'],
                'hAlign' => 'center',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->itemPaymentOrder->description;
                }
            ],

            [
                'attribute' => 'quantity',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM,
                'filterType' => GridView::FILTER_NUMBER,
                'filterWidgetOptions' => [
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
                    $quantity = $data->itemPaymentOrder->quantity;
                    return GlobalFunctions::formatNumber($quantity, 0);
                },
                'format' => 'html',
            ],

            [
                'class' => 'kartik\grid\EditableColumn',
                'attribute' => 'received',
                'value' => function ($data) {
                    return (isset($data->received)) ? GlobalFunctions::formatNumber($data->received, 0) : '---';
                },
                'refreshGrid' => true,
                'editableOptions' => function ($model, $key, $index) {
                    return [
                        'format' => Editable::FORMAT_BUTTON,
                        'buttonsTemplate' => '{submit}',
                        'formOptions' => ['action' => ['/reception-item-po/editable_ajax']],
                        'asPopover' => true,
                        'inputType' => Editable::INPUT_SPIN,
                        'options' => [
                            'pluginOptions' => [
                                'autofocus' => 'autofocus',
                                'min' => 0,
                                //'step' => 5
                            ]
                        ]
                    ];
                },
                'headerOptions' => ['class' => 'custom_width'],
                'contentOptions' => ['class' => 'custom_width'],
                'vAlign' => 'middle',
                'width' => '100px',
                'pageSummaryFunc' => GridView::F_SUM,
                'filterType' => GridView::FILTER_NUMBER,
                'filterWidgetOptions' => [
                    'maskedInputOptions' => [
                        'allowMinus' => false,
                        'groupSeparator' => '.',
                        'radixPoint' => ',',
                        'digits' => 0
                    ],
                ],
            ],

            [
                'label' => Yii::t('backend', 'Diferencias'),
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM,
                'filterType' => GridView::FILTER_NUMBER,
                'filterWidgetOptions' => [
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
                    $quantity = $data->itemPaymentOrder->quantity;
                    $result = '---';
                    if (isset($data->received) && !empty($data->received)) {
                        $received = $data->received;
                        $diff = 0;

                        if ($received !== $quantity) {
                            if ($received > $quantity) {
                                $diff = $received - $quantity;
                                $result = '<span class="badge bg-orange"> + ' . GlobalFunctions::formatNumber($diff, 0) . ' </span>';
                            } else {
                                $diff = $quantity - $received;
                                $result = '<span class="badge bg-red"> - ' . GlobalFunctions::formatNumber($diff, 0) . ' </span>';
                            }
                        } else {
                            $result = '<span class="badge bg-green"> OK </span>';
                        }
                    }

                    return $result;
                },
                'format' => 'html',
            ],

            [
                'class' => 'kartik\grid\ActionColumn',
                'header' => Yii::t('backend', 'Imagen'),
                'dropdown' => false,
                'vAlign'=>'middle',
                'dropdownOptions' => ['class' => 'float-right'],
                'headerOptions' => ['class' => 'kartik-sheet-style'],
                'template' => Helper::filterActionColumn(['image']),
                'buttons' => [
                    'image' => function($url, $model) {

                        if(isset($model->itemPaymentOrder->product_id))
                        {
                            $image = '<i class="fa fa-image modalClickImage" data-href="'. $model->itemPaymentOrder->product->getPreview().'"></i>';
                        }
                        else
                        {
                            $image = '';
                        }
                        return $image;
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
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'emptyCell' => '',
                                'id' => 'reception_item_po',
                                'autoXlFormat' => true,
                                'export' => [
                                    'fontAwesome' => true,
                                    'showConfirmAlert' => false,
                                    'target' => GridView::TARGET_BLANK
                                ],
                                'floatHeader' => false,
                                'showPageSummary' => false,
                                'pjax' => true,
                                'pjaxSettings' => [
                                    'options' => [
                                        'enablePushState' => false,
                                    ],
                                ],
                                'panel' => $panels,
                                'toolbar' => $toolbars,
                            ],
                            'options' => ['id' => 'grid-reception_item_po'] // a unique identifier is important
                        ]);

                        DynaGrid::end();
                        ?>            </div>
                </div>
            </div>
            <!-- /.box-header -->
        </div>
    </div>

    <?php
}
?>

    <div id="panel-form-reception_item_po">

    </div>


<?php
// Register action buttons js
$this->registerJs('

$(document).ready(function(e) {
	function init_click_handlers_reception_item_po(){
		
		$("a.btn-update_reception_item_po").click(function(e) {
			e.preventDefault();
            var selectedId = $("#reception_item_po").yiiGridView("getSelectedRows");
            if(selectedId.length == 0) {
				bootbox.alert("Seleccione un elemento");                
            } else if(selectedId.length>1){
				bootbox.alert("Solo puede seleccionar un elemento");          				
            } else {
				var url = "' . Url::to(['/reception-item-po/update_ajax'], GlobalFunctions::URLTYPE) . '"; 	
				$.ajax({
					type: "GET",
					url : url,     
					data : {id: selectedId[0]},
					success : function(data) {
						$("#panel-grid-reception_item_po").hide(500);
						$("#panel-form-reception_item_po").html(data);
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
		
		        
        $("a.btn-refresh_reception_item_po").click(function(e) {
			e.preventDefault();
            $.pjax.reload({container:"#grid-reception_item_po-pjax"});
        });
       
	}
	
	function appendClickImage(){
            $(\'.modalClickImage\').click(function (e) {
                e.preventDefault();
                var img = \'<div class="text-center img-bordered"><img style="width: 100%;" src="\'+$(this).attr(\'data-href\')+\'"></div>\';
                $(\'#modal\').modal(\'show\').find(\'#modalContent\').html(img);
            });
        }

	init_click_handlers_reception_item_po(); //first run
	appendClickImage();
	$("#grid-reception_item_po-pjax").on("pjax:success", function() {
	    init_click_handlers_reception_item_po(); //reactivate links in grid after pjax update
	    appendClickImage();
	});
});
');
?>