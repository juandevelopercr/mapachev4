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
use backend\models\business\Supplier;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use backend\models\business\PaymentOrder;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\PaymentOrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = Yii::t('backend', 'Órdenes de compra');
$this->params['breadcrumbs'][] = $this->title;

$create_button='';
?>

<?php 
	if (Helper::checkRoute($controllerId . 'create')) {
		$create_button =
            '<span class="kv-grid-toolbar">
                    <div class="btn-group">
                      <button class="btn btn-default dropdown-toggle margin" title="Orden de Compra" data-toggle="dropdown"><i class="glyphicon glyphicon-envelope"></i> Enviar Orden <span class="caret"></span></button>
                      <ul class="dropdown-menu dropdown-menu-right">
                        <li title="Enviar Pdf Orden de Compra Colones">'.
                            Html::a("<i class='fa fa-file-pdf-o'></i> Orden de Compra ¢ Original",'#', ['class'=>'send_btn-pdf-colones-original']).'
                        </li>
                        <li title="Enviar Pdf Orden de Compra Colones">'.
                            Html::a("<i class='fa fa-file-pdf-o'></i> Orden de Compra ¢ Copia",'#', ['class'=>'send_btn-pdf-colones-copia']).'
                        </li>								
                        <li title="Enviar Pdf Orden de Compra Dolar">'.
                            Html::a("<i class='fa fa-file-pdf-o'></i> Orden de Compra $ Original","#", ['class'=>'send_btn-pdf-dolar-original']).'
                        </li>
                        <li title="Enviar Pdf Orden de Compra Dolar">'.
                            Html::a("<i class='fa fa-file-pdf-o'></i> Orden de Compra $ Copia","#", ['class'=>'send_btn-pdf-dolar-copia']).'
                        </li>								
                      </ul>
                    </div>					
            </span>'.
            '<span class="kv-grid-toolbar">
                    <div class="btn-group">
                      <button class="btn btn-default dropdown-toggle margin" title="Orden de Compra" data-toggle="dropdown"><i class="glyphicon glyphicon-print"></i> Orden de Compra <span class="caret"></span></button>
                      <ul class="dropdown-menu dropdown-menu-right">
                        <li title="Pdf Orden de Compra Colones">'.
                            Html::a("<i class='fa fa-file-pdf-o'></i> Orden de Compra ¢ Original",'#', ['class'=>'btn-pdf-colones-original']).'
                        </li>
                        <li title="Pdf Orden de Compra Colones">'.
                            Html::a("<i class='fa fa-file-pdf-o'></i> Orden de Compra ¢ Copia",'#', ['class'=>'btn-pdf-colones-copia']).'
                        </li>								
                        <li title="Pdf Orden de Compra Dolar">'.
                            Html::a("<i class='fa fa-file-pdf-o'></i> Orden de Compra $ Original","#", ['class'=>'btn-pdf-dolar-original']).'
                        </li>
                        <li title="Pdf Orden de Compra Dolar">'.
                            Html::a("<i class='fa fa-file-pdf-o'></i> Orden de Compra $ Copia","#", ['class'=>'btn-pdf-dolar-copia']).'
                        </li>								
                      </ul>
                    </div>					
            </span>'.
            Html::a('<i class="fa fa-plus"></i> '.Yii::t('backend', 'Crear'), ['create'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Crear').' '.Yii::t('backend', 'Orden de compra')]);
	}


$custom_elements_gridview = new Custom_Settings_Column_GridView($create_button,$dataProvider,['view','update','delete']);
?>

    <div class="box-body">
        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
        <?= GridView::widget([
            'id'=>'grid_payment_order',
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
					'attribute'=>'number',
					'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
					'hAlign'=>'center',
					'format'=> 'html',
					'value' => function ($data) {
						return $data->number;
					}
				],

                [
                    'attribute'=>'supplier_id',
                    'format' => 'html',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'filterType'=>GridView::FILTER_SELECT2,
                    'filter'=> Supplier::getSelectMap(),
                    'filterWidgetOptions' => [
                        'pluginOptions'=>['allowClear'=>true],
                        'options'=>['multiple'=>false],
                    ],
                    'value'=> 'supplier.name',
                    'filterInputOptions'=>['placeholder'=> '------'],
                    'hAlign'=>'center',
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
					'attribute'=>'require_date',
                    'value' => function($data){
                        return GlobalFunctions::formatDateToShowInSystem($data->require_date);
                    },
					'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
					'hAlign'=>'center',
					'filterType' => GridView::FILTER_DATE_RANGE,
					'filterWidgetOptions' => ([
						'model' => $searchModel,
						'attribute' => 'require_date',
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
                    'attribute'=>'status_payment_order_id',
                    'format' => 'html',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'filterType'=>GridView::FILTER_SELECT2,
                    'filter'=> UtilsConstants::getStatusPaymentOrderSelectMap(),
                    'filterWidgetOptions' => [
                        'pluginOptions'=>['allowClear'=>true],
                        'options'=>['multiple'=>false],
                    ],
                    'value'=> function($model){ return UtilsConstants::getStatusPaymentOrderSelectMap($model->status_payment_order_id,false,true); },
                    'filterInputOptions'=>['placeholder'=> '------'],
                    'hAlign'=>'center',
                ],

                [
                    'attribute'=>'payout_status',
                    'format' => 'html',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'filterType'=>GridView::FILTER_SELECT2,
                    'filter'=> UtilsConstants::getPayoutStatusSelectType(),
                    'filterWidgetOptions' => [
                        'pluginOptions'=>['allowClear'=>true],
                        'options'=>['multiple'=>false],
                    ],
                    'value'=> function($model){
                            return (isset($model->payout_status))? UtilsConstants::getPayoutStatusSelectType($model->payout_status): null;
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
    $js = Footer_Bulk_Delete::getFooterBulkDelete($url,'grid_payment_order');
    $this->registerJs($js, View::POS_READY);
?>

<?php
// Register action buttons js
$this->registerJs('
    $(document).ready(function()
    {

	function init_click_handlers(){
				
		$("a.btn-pdf-colones-original").click(function(e) {
			e.preventDefault();
            var selectedId = $("#grid_payment_order").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
                bootbox.alert("Seleccione un elemento"); 
            } else {
                var url = "'.Url::to(['/payment-order/viewpdfcolonesoriginal'], GlobalFunctions::URLTYPE).'?id="+selectedId;
                window.open(url,"_blank");
            }
        });	
		
        $("a.btn-pdf-dolar-original").click(function(e) {
			e.preventDefault();
            var selectedId = $("#grid_payment_order").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
                bootbox.alert("Seleccione un elemento"); 
            } else {
                var url = "'.Url::to(['/payment-order/viewpdfdolaroriginal'], GlobalFunctions::URLTYPE).'?id="+selectedId;
                window.open(url,"_blank");
            }
        });	
		
		$("a.btn-pdf-colones-copia").click(function(e) {
			e.preventDefault();
            var selectedId = $("#grid_payment_order").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
                bootbox.alert("Seleccione un elemento"); 
            } else {
                var url = "'.Url::to(['/payment-order/viewpdfcolonescopia'], GlobalFunctions::URLTYPE).'?id="+selectedId;
                window.open(url,"_blank");
            }
        });	
		
        $("a.btn-pdf-dolar-copia").click(function(e) {
			e.preventDefault();
            var selectedId = $("#grid_payment_order").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
                bootbox.alert("Seleccione un elemento"); 
            } else {
                var url = "'.Url::to(['/payment-order/viewpdfdolarcopia'], GlobalFunctions::URLTYPE).'?id="+selectedId;
                window.open(url,"_blank");
            }
        });	
        
        
        $("a.send_btn-pdf-colones-original").click(function(e) {
			e.preventDefault();
            var selectedId = $("#grid_payment_order").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
                bootbox.alert("Seleccione al menos un elemento"); 
            } else {
                var url = "'.Url::to(['/payment-order/send_pdf'], GlobalFunctions::URLTYPE).'?type='.UtilsConstants::PDF_ORIGINAL_COLON_TYPE.'&ids="+selectedId;
                window.open(url,"_parent");
            }
        });	
		
        $("a.send_btn-pdf-dolar-original").click(function(e) {
			e.preventDefault();
            var selectedId = $("#grid_payment_order").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
                bootbox.alert("Seleccione al menos un elemento"); 
            } else {
                var url = "'.Url::to(['/payment-order/send_pdf'], GlobalFunctions::URLTYPE).'?type='.UtilsConstants::PDF_ORIGINAL_DOLLAR_TYPE.'&ids="+selectedId;
                window.open(url,"_parent");
            }
        });	
		
		$("a.send_btn-pdf-colones-copia").click(function(e) {
			e.preventDefault();
            var selectedId = $("#grid_payment_order").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
                bootbox.alert("Seleccione al menos un elemento"); 
            } else {
                var url = "'.Url::to(['/payment-order/send_pdf'], GlobalFunctions::URLTYPE).'?type='.UtilsConstants::PDF_COPY_COLON_TYPE.'&ids="+selectedId;
                window.open(url,"_parent");
            }
        });	
		
        $("a.send_btn-pdf-dolar-copia").click(function(e) {
			e.preventDefault();
            var selectedId = $("#grid_payment_order").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
                bootbox.alert("Seleccione al menos un elemento"); 
            } else {
                var url = "'.Url::to(['/payment-order/send_pdf'], GlobalFunctions::URLTYPE).'?type='.UtilsConstants::PDF_COPY_DOLLAR_TYPE.'&ids="+selectedId;
                window.open(url,"_parent");
            }
        });		
	
	}
    init_click_handlers(); //first run
    $("#grid_payment_order-pjax").on("pjax:success", function() {
      init_click_handlers(); //reactivate links in grid after pjax update
    });
});
');

?>

