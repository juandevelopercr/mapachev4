<?php

use yii\helpers\Html;
//use kartik\grid\GridView;
use common\widgets\GridView;
use mdm\admin\components\Helper;
use yii\web\View;
use yii\helpers\Url;
use backend\components\Footer_Bulk_Delete;
use backend\components\Custom_Settings_Column_GridView;
use common\models\GlobalFunctions;
use backend\models\business\Customer;
use backend\models\business\CollectorHasPurchaseOrder;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use backend\models\nomenclators\RouteTransport;
use common\models\User;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\PurchaseOrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = Yii::t('backend', 'Órdenes de pedido');
$this->params['breadcrumbs'][] = $this->title;

$create_button='';
?>

<?php 
	if (Helper::checkRoute($controllerId . 'create')) {
		$create_button =
            Html::a('<i class="fa fa-file-pdf-o"></i> '.Yii::t('backend', 'Preparación'), '#', ['class' => 'btn btn-default btn-flat margin btn-pdf-preparation', 'title' => Yii::t('backend', 'Reporte de preparación de mercancías')])
            .' '.Html::a('<i class="fa fa-plus"></i> '.Yii::t('backend', 'Crear'), ['create'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Crear').' '.Yii::t('backend', 'Orden de pedido')])
        ;
	}
    $custom_buttons = [
     
    'clone' => function($url, $model) {
        $options = [
            'title' => Yii::t('backend', 'Clonar'),
            'class' => 'btn btn-xs btn-default btn-flat',
            'aria-label' => Yii::t('backend', 'Clonar'),
            'data-confirm' => Yii::t('backend', '¿Seguro desea clonar este elemento?'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',
        ];
        return Html::a('<i class="fa fa-clone"></i>', $url, $options);
    },
    'invoice' => function($url, $model) {
        $options = [
            'title' => Yii::t('backend', 'Facturar'),
            'class' => 'btn btn-xs btn-default btn-flat',
            'aria-label' => Yii::t('backend', 'Facturar'),
            'data-confirm' => Yii::t('backend', '¿Seguro desea convertir en factura esta orden de pedido?'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',
        ];
        if ($model->status != UtilsConstants::PURCHASE_ORDER_STATUS_FINISHED)
            return Html::a('<i class="fa fa-list-alt"></i>', Url::to(['/purchase-order/facturar','id'=>$model->id], GlobalFunctions::URLTYPE), $options);
    },


    'update' => function($url, $model) {
        $options = [
            'title' => Yii::t('backend', 'Actualizar'),
            'class' => 'btn btn-xs btn-default btn-flat',
            'aria-label' => Yii::t('backend', 'Actualizar'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',
        ];
        if ($model->status != UtilsConstants::PURCHASE_ORDER_STATUS_FINISHED)
        {
            return Html::a('<i class="glyphicon glyphicon-pencil"></i>', Url::to(['/purchase-order/update', 'id' => $model->id], GlobalFunctions::URLTYPE), $options);
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
        if ($model->status != UtilsConstants::PURCHASE_ORDER_STATUS_FINISHED)
        {
            return Html::a('<i class="glyphicon glyphicon-trash"></i>', Url::to(['/purchase-order/delete', 'id' => $model->id], GlobalFunctions::URLTYPE), $options);
        }
    },    
];
	$custom_elements_gridview = new Custom_Settings_Column_GridView($create_button,$dataProvider,['view','clone','update','invoice','delete'],$custom_buttons);
?>

    <div class="box-body">
        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
        <?= GridView::widget([
            'id'=>'grid_purchase_order',
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
                        return Html::a($data->consecutive, Url::to(['/purchase-order/update', 'id' => $data->id], GlobalFunctions::URLTYPE));
                    }
				],

				[
					'attribute'=>'customer_id',
                    'format' => 'html',
                    'headerOptions' => ['class'=>'custom_width'],
                    'contentOptions' => ['class'=>'custom_width'],
					'filterType'=>GridView::FILTER_SELECT2,
					'filter'=> Customer::getSelectMap(),
					'filterWidgetOptions' => [
						'pluginOptions'=>['allowClear'=>true],
						'options'=>['multiple'=>false],
					],
					'value'=> 'customer.name',
					'filterInputOptions'=>['placeholder'=> '------'],
					'hAlign'=>'center',
				],
				[
                    'attribute'=>'commercial_name',
                    'headerOptions' => ['class'=>'custom_width'],
                    'contentOptions' => ['class'=>'custom_width'],
					'hAlign'=>'center',
                    'value' => function($data){
                        return $data->customer->commercial_name;
                    },
				],                             

                [
                    'attribute'=>'request_date',
                    'value' => function($data){
                        return GlobalFunctions::formatDateToShowInSystem($data->request_date);
                    },
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'hAlign'=>'center',
                    'filterType' => GridView::FILTER_DATE_RANGE,
                    'filterWidgetOptions' => ([
                        'model' => $searchModel,
                        'attribute' => 'request_date',
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
                    'attribute'=>'collectors',
                    'format' => 'html',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'filterType'=>GridView::FILTER_SELECT2,
                    'filter'=> User::getSelectMapAgents(true,true),
                    'filterWidgetOptions' => [
                        'pluginOptions'=>['allowClear'=>true],
                        'options'=>['multiple'=>false],
                    ],
                    'value'=> function($model){
                        return CollectorHasPurchaseOrder::getCollectorStringByPurchaseOrder($model->id);
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

                [
                    'attribute'=>'status',
                    'format' => 'html',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'filterType'=>GridView::FILTER_SELECT2,
                    'filter'=> UtilsConstants::getPurchaseOrderStatusSelectType(),
                    'filterWidgetOptions' => [
                        'pluginOptions'=>['allowClear'=>true],
                        'options'=>['multiple'=>false],
                    ],
                    'value'=> function($data){
                        return UtilsConstants::getPurchaseOrderStatusSelectType($data->status);
                    },
                    'filterInputOptions'=>['placeholder'=> '------'],
                    'hAlign'=>'center',
                ],

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
    $js = Footer_Bulk_Delete::getFooterBulkDelete($url,'grid_purchase_order');
    $this->registerJs($js, View::POS_READY);
?>

<?php
// Register action buttons js
$this->registerJs('
    $(document).ready(function()
    {
        function init_click_handlers(){
                    
            $("a.btn-pdf-preparation").click(function(e) {
                e.preventDefault();
                var selectedId = $("#grid_purchase_order").yiiGridView("getSelectedRows");
    
                if(selectedId.length == 0) {
                    bootbox.alert("Seleccione al menos un elemento"); 
                } else {
                    var url = "'.Url::to(['/purchase-order/preparation_pdf'], GlobalFunctions::URLTYPE).'?ids="+selectedId;
                    window.open(url,"_blank");
                }
            });	
        
        }
        
    init_click_handlers(); //first run
    $("#grid_purchase_order-pjax").on("pjax:success", function() {
      init_click_handlers(); //reactivate links in grid after pjax update
    });
});
');

?>
