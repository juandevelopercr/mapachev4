<?php

use yii\helpers\Html;
use common\widgets\GridView;
use mdm\admin\components\Helper;
use yii\web\View;
use yii\helpers\Url;
use backend\components\Footer_Bulk_Delete;
use backend\components\Custom_Settings_Column_GridView;
use common\models\GlobalFunctions;
use yii\helpers\BaseStringHelper;
use backend\models\business\Product;
use common\models\User;
use backend\models\nomenclators\BranchOffice;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\AdjustmentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = Yii::t('backend', 'Ajustes');
$this->params['breadcrumbs'][] = $this->title;

$create_button='';
?>

<?php 
	if (Helper::checkRoute($controllerId . 'create')) {
		$create_button = Html::a('<i class="fa fa-plus"></i> '.Yii::t('backend', 'Crear'), ['create'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Crear').' '.Yii::t('backend', 'Ajuste')]);
	}

	$custom_elements_gridview = new Custom_Settings_Column_GridView($create_button,$dataProvider,['view']);
	$custom_panel = [
        'type' => 'default',
        'after'=>
            Html::a('<i class="fa fa-refresh"></i> '.Yii::t('backend','Resetear'), ['index'], ['class' => 'btn btn-default btn-flat margin','title'=> Yii::t('backend','Resetear listado'), 'data-toggle' => 'tooltip']),
        'before' => '',
    ];
	$custom_elements_gridview->setPanel($custom_panel);
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
						return $data->consecutive;
					}
				],
                                                        
				[
					'attribute'=>'product_id',
                    'format' => 'html',
					'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
					'filterType'=>GridView::FILTER_SELECT2,
					'filter'=> Product::getSelectMap(),
					'filterWidgetOptions' => [
						'pluginOptions'=>['allowClear'=>true],
						'options'=>['multiple'=>false],
					],
					'value'=> function($model){
                        return $model->product->bar_code. ' - '.$model->product->description;
                    },
					'filterInputOptions'=>['placeholder'=> '------'],
					'hAlign'=>'center',
				],

                [
                    'attribute'=>'origin_branch_office_id',
                    'label' => Yii::t('backend','Sucursal'),
                    'format' => 'html',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'filterType'=>GridView::FILTER_SELECT2,
                    'filter'=> BranchOffice::getSelectMap(),
                    'filterWidgetOptions' => [
                        'pluginOptions'=>['allowClear'=>true],
                        'options'=>['multiple'=>false],
                    ],
                    'value'=> function($model){
                        return $model->originBranchOffice->code. ' - '.$model->originBranchOffice->name;
                    },
                    'filterInputOptions'=>['placeholder'=> '------'],
                    'hAlign'=>'center',
                ],

				[
					'attribute'=>'past_quantity',
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
                        return GlobalFunctions::formatNumber($data->past_quantity,2);
                    },
                    'format' => 'html',
				],
                                     
				[
					'attribute'=>'entry_quantity',
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
                        return GlobalFunctions::formatNumber($data->entry_quantity,2);
                    },
                    'format' => 'html',
				],

                [
                    'attribute'=>'new_quantity',
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
                        return GlobalFunctions::formatNumber($data->new_quantity,2);
                    },
                    'format' => 'html',
                ],

				[
					'attribute'=>'user_id',
                    'format' => 'html',
					'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
					'filterType'=>GridView::FILTER_SELECT2,
					'filter'=> User::getSelectMap(),
					'filterWidgetOptions' => [
						'pluginOptions'=>['allowClear'=>true],
						'options'=>['multiple'=>false],
					],
                    'value'=> function($model){ return User::getFullNameByUserId($model->user_id); },
                    'filterInputOptions'=>['placeholder'=> '------'],
					'hAlign'=>'center',
				],

                [
                    'attribute'=>'observations',
                    'headerOptions' => ['class'=>'custom_width'],
                    'contentOptions' => ['class'=>'custom_width'],
                    'format'=> 'html',
                    'value' => function ($data) {
                        return $data->observations;
                    }
                ],

                [
                    'attribute'=>'created_at',
                    'value' => function($data){
                        return GlobalFunctions::formatDateToShowInSystem($data->created_at);
                    },
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'hAlign'=>'center',
                    'filterType' => GridView::FILTER_DATE_RANGE,
                    'filterWidgetOptions' => ([
                        'model' => $searchModel,
                        'attribute' => 'created_at',
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

                                        
				$custom_elements_gridview->getActionColumn(),

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

