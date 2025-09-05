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
use yii\helpers\BaseStringHelper;
use backend\models\nomenclators\Cabys;
use backend\models\nomenclators\Family;
use backend\models\nomenclators\Category;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\BranchOffice;
use backend\models\business\Supplier;
use backend\models\nomenclators\InventoryType;
use backend\models\nomenclators\TaxType;
use backend\models\nomenclators\TaxRateType;
use backend\models\nomenclators\ExonerationDocumentType;
use backend\models\business\ProductHasBranchOffice;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = Yii::t('backend', 'Productos');
$this->params['breadcrumbs'][] = $this->title;

$create_button='';
?>

<?php 
	if (Helper::checkRoute($controllerId . 'create')) {
		$create_button = Html::a('<i class="fa fa-plus"></i> '.Yii::t('backend', 'Crear'), ['create'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Crear').' '.Yii::t('backend', 'Producto')]);
	}

	$custom_elements_gridview = new Custom_Settings_Column_GridView($create_button,$dataProvider);
?>

<?= GlobalFunctions::showModalHtmlContent(Yii::t('backend','Imagen'),'modal-lg') ?>

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
                    'attribute'=>'image',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'hAlign'=>'center',
                    'format'=> 'html',
                    'value' => function ($data) {
                        return '<img class="preview-index img-bordered modalClickImage" src="'. $data->getPreview() .'">';
                    },
                    'filter' => false
                ],

                [
                    'attribute'=>'code',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'hAlign'=>'center',
                    'format'=> 'html',
                    'value' => function ($data) {
                        return $data->code;
                    }
                ],

                [
                    'attribute'=>'bar_code',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'hAlign'=>'center',
                    'format'=> 'html',
                    'value' => function ($data) {
                        return $data->bar_code;
                    }
                ],

                [
                    'attribute'=>'supplier_code',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'hAlign'=>'center',
                    'format'=> 'html',
                    'value' => function ($data) {
                        return $data->supplier_code;
                    }
                ],

				[
					'attribute'=>'description',
                    'headerOptions' => ['class'=>'custom_width'],
                    'contentOptions' => ['class'=>'custom_width'],
					'format'=> 'html',
					'value' => function ($data) {
						return $data->description;
					}
				],
                [
                    'attribute'=>'initial_existence',
                    'contentOptions'=>['class'=>'kv-align-left kv-align-middle'],
                    'value' => function ($data) {
                        return GlobalFunctions::formatNumber($data->getExistence(),2);
                    },
                    'format' => 'html',
                ],
                [
                    'attribute'=>'price',
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
                        return GlobalFunctions::formatNumber($data->price,2);
                    },
                    'format' => 'html',
                ],

                [
                    'attribute'=>'price1',
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
                        return (isset($data->price1))? GlobalFunctions::formatNumber($data->price1,2): '';
                    },
                    'format' => 'html',
                ],

                [
                    'attribute'=>'price_detail',
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
                        return (isset($data->price_detail))? GlobalFunctions::formatNumber($data->price_detail,2): '';
                    },
                    'format' => 'html',
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
    $js = Footer_Bulk_Delete::getFooterBulkDelete($url);
    $this->registerJs($js, View::POS_READY);

    $js_click_image = <<< JS
                $(document).ready(function(){
                    
                    function appendClickImage(){
                        $('.modalClickImage').click(function (e) {
                            e.preventDefault();
                            var img = '<div class="text-center img-bordered"><img style="width: 100%;" src="'+$(this).attr('src')+'"></div>';
                            $('#modal').modal('show').find('#modalContent').html(img);
                        });
                    }
                    
                    appendClickImage();
                    $(document).on("pjax:complete", function(){
                        appendClickImage();    
                    });
                });
JS;
    $this->registerJs($js_click_image, View::POS_READY);
?>

