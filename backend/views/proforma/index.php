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
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use common\models\User;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\ProformaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = Yii::t('backend', 'Proformas');
$this->params['breadcrumbs'][] = $this->title;

$create_button='';
?>

<?php 
	if (Helper::checkRoute($controllerId . 'create')) {
		$create_button =
            '<span class="kv-grid-toolbar">
                    <div class="btn-group">
                      <button class="btn btn-default dropdown-toggle margin" title="Proforma" data-toggle="dropdown"><i class="glyphicon glyphicon-envelope"></i> Enviar Proforma <span class="caret"></span></button>
                      <ul class="dropdown-menu dropdown-menu-right">
                        <li title="Enviar Pdf Proforma Colones">'.
                             Html::a("<i class='fa fa-file-pdf-o'></i> Proforma ¢ Original",'#', ['class'=>'send_btn-pdf-colones-original']).'
                        </li>
                        <li title="Enviar Pdf Proforma Colones">'.
                             Html::a("<i class='fa fa-file-pdf-o'></i> Proforma ¢ Copia",'#', ['class'=>'send_btn-pdf-colones-copia']).'
                        </li>								
                        <li title="Enviar Pdf Proforma Dolar">'.
                             Html::a("<i class='fa fa-file-pdf-o'></i> Proforma $ Original","#", ['class'=>'send_btn-pdf-dolar-original']).'
                        </li>
                        <li title="Enviar Pdf Proforma Dolar">'.
                             Html::a("<i class='fa fa-file-pdf-o'></i> Proforma $ Copia","#", ['class'=>'send_btn-pdf-dolar-copia']).'
                        </li>								
                      </ul>
                    </div>					
            </span>'.
            '<span class="kv-grid-toolbar">
                    <div class="btn-group">
                      <button class="btn btn-default dropdown-toggle margin" title="Proforma" data-toggle="dropdown"><i class="glyphicon glyphicon-print"></i> Proforma <span class="caret"></span></button>
                      <ul class="dropdown-menu dropdown-menu-right">
                        <li title="Pdf Proforma Colones">'.
                            Html::a("<i class='fa fa-file-pdf-o'></i> Proforma ¢ Original",'#', ['class'=>'btn-pdf-colones-original']).'
                        </li>
                        <li title="Pdf Proforma Colones">'.
                            Html::a("<i class='fa fa-file-pdf-o'></i> Proforma ¢ Copia",'#', ['class'=>'btn-pdf-colones-copia']).'
                        </li>								
                        <li title="Pdf Proforma Dolar">'.
                            Html::a("<i class='fa fa-file-pdf-o'></i> Proforma $ Original","#", ['class'=>'btn-pdf-dolar-original']).'
                        </li>
                        <li title="Pdf Proforma Dolar">'.
                            Html::a("<i class='fa fa-file-pdf-o'></i> Proforma $ Copia","#", ['class'=>'btn-pdf-dolar-copia']).'
                        </li>								
                      </ul>
                    </div>					
            </span>'.
            Html::a('<i class="fa fa-plus"></i> '.Yii::t('backend', 'Crear'), ['create'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Crear').' '.Yii::t('backend', 'Proforma')]);
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
            'data-confirm' => Yii::t('backend', '¿Seguro desea convertir en factura esta proforma?'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',
        ];
        if (is_null($model->facturada) || $model->facturada != 1)
            return Html::a('<i class="fa fa-list-alt"></i>', Url::to(['/proforma/facturar','id'=>$model->id], GlobalFunctions::URLTYPE), $options);
    }
];
	$custom_elements_gridview = new Custom_Settings_Column_GridView($create_button,$dataProvider,['view','clone','update','invoice','delete'],$custom_buttons);
?>

    <div class="box-body">
        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
        <?= GridView::widget([
            'id'=>'grid',
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
                        return Html::a($data->consecutive, ['/proforma/update', 'id' => $data->id]);
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
                    'attribute'=>'seller_id',
                    'format' => 'html',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'filterType'=>GridView::FILTER_SELECT2,
                    'filter'=> User::getSelectMapAgents(true,true),
                    'filterWidgetOptions' => [
                        'pluginOptions'=>['allowClear'=>true],
                        'options'=>['multiple'=>false],
                    ],
                    'value'=> function($model){
                        return (isset($model->seller_id) && !empty($model->seller_id))? User::getFullNameByUserId($model->seller_id) : null;
                    },
                    'filterInputOptions'=>['placeholder'=> '------'],
                    'hAlign'=>'center',
                ],

                [
                    'attribute'=>'status',
                    'format' => 'html',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'filterType'=>GridView::FILTER_SELECT2,
                    'filter'=> UtilsConstants::getProformaStatusSelectType(),
                    'filterWidgetOptions' => [
                        'pluginOptions'=>['allowClear'=>true],
                        'options'=>['multiple'=>false],
                    ],
                    'value'=> function($data){
                        return UtilsConstants::getProformaStatusSelectType($data->status);
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

                
                [
                    'attribute'=>'facturada',
                    'contentOptions'=>['class'=>'kv-align-center kv-align-middle'],
                    'hAlign'=>'center',
                    'format'=> 'html',
                    'value' => function ($data) {
                        $html = '<i class="fa fa-minus-square-o" aria-hidden="true"></i>';
                        if (!is_null($data->facturada) && $data->facturada == 1)
                            $html = "<i class=\"fa fa-check\" aria-hidden=\"true\"></i>";

                        return $html;
                    }
                ],
            ],

            'toolbar' =>  $custom_elements_gridview->getToolbar(),

            'panel' => $custom_elements_gridview->getPanel(),

            'toggleDataOptions' => $custom_elements_gridview->getTogleDataOptions(),
        ]); ?>
    </div>

<?php
    $url = Url::to([$controllerId.'multiple_delete'], GlobalFunctions::URLTYPE);
    $js = Footer_Bulk_Delete::getFooterBulkDelete($url);
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
            var selectedId = $("#grid").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
                bootbox.alert("Seleccione al menos un elemento"); 
            } else {
                var url = "'.Url::to(['/proforma/viewpdfcolonesoriginal'], GlobalFunctions::URLTYPE).'?id="+selectedId;
                window.open(url,"_blank");
            }
        });	
		
        $("a.btn-pdf-dolar-original").click(function(e) {
			e.preventDefault();
            var selectedId = $("#grid").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
                bootbox.alert("Seleccione al menos un elemento"); 
            } else {
                var url = "'.Url::to(['/proforma/viewpdfdolaroriginal'], GlobalFunctions::URLTYPE).'?id="+selectedId;
                window.open(url,"_blank");
            }
        });	
		
		$("a.btn-pdf-colones-copia").click(function(e) {
			e.preventDefault();
            var selectedId = $("#grid").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
                bootbox.alert("Seleccione al menos un elemento"); 
            } else {
                var url = "'.Url::to(['/proforma/viewpdfcolonescopia']).'?id="+selectedId;
                window.open(url,"_blank");
            }
        });	
		
        $("a.btn-pdf-dolar-copia").click(function(e) {
			e.preventDefault();
            var selectedId = $("#grid").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
                bootbox.alert("Seleccione al menos un elemento"); 
            } else {
                var url = "'.Url::to(['/proforma/viewpdfdolarcopia'], GlobalFunctions::URLTYPE).'?id="+selectedId;
                window.open(url,"_blank");
            }
        });	
        
        
        
        
        $("a.send_btn-pdf-colones-original").click(function(e) {
			e.preventDefault();
            var selectedId = $("#grid").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
                bootbox.alert("Seleccione al menos un elemento"); 
            } else {
                var url = "'.Url::to(['/proforma/send_pdf'], GlobalFunctions::URLTYPE).'?type='.UtilsConstants::PDF_ORIGINAL_COLON_TYPE.'&ids="+selectedId;
                window.open(url,"_parent");
            }
        });	
		
        $("a.send_btn-pdf-dolar-original").click(function(e) {
			e.preventDefault();
            var selectedId = $("#grid").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
                bootbox.alert("Seleccione al menos un elemento"); 
            } else {
                var url = "'.Url::to(['/proforma/send_pdf'], GlobalFunctions::URLTYPE).'?type='.UtilsConstants::PDF_ORIGINAL_DOLLAR_TYPE.'&ids="+selectedId;
                window.open(url,"_parent");
            }
        });	
		
		$("a.send_btn-pdf-colones-copia").click(function(e) {
			e.preventDefault();
            var selectedId = $("#grid").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
                bootbox.alert("Seleccione al menos un elemento"); 
            } else {
                var url = "'.Url::to(['/proforma/send_pdf'], GlobalFunctions::URLTYPE).'?type='.UtilsConstants::PDF_COPY_COLON_TYPE.'&ids="+selectedId;
                window.open(url,"_parent");
            }
        });	
		
        $("a.send_btn-pdf-dolar-copia").click(function(e) {
			e.preventDefault();
            var selectedId = $("#grid").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
                bootbox.alert("Seleccione al menos un elemento"); 
            } else {
                var url = "'.Url::to(['/proforma/send_pdf'], GlobalFunctions::URLTYPE).'?type='.UtilsConstants::PDF_COPY_DOLLAR_TYPE.'&ids="+selectedId;
                window.open(url,"_parent");
            }
        });	
       	
	}
	
    init_click_handlers(); //first run
    $("#grid-pjax").on("pjax:success", function() {
      init_click_handlers(); //reactivate links in grid after pjax update
    });
});
');

?>
