<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use mdm\admin\components\Helper;
use yii\web\View;
use yii\helpers\Url;
use backend\components\Footer_Bulk_Delete;
use backend\components\Custom_Settings_Column_GridView;
use backend\models\business\MovementCashRegister;
use backend\models\nomenclators\MovementTypes;
use common\models\GlobalFunctions;
use yii\helpers\BaseStringHelper;
use backend\models\nomenclators\BranchOffice;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\CashRegisteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/' . $this->context->uniqueId . '/';
$this->title = Yii::t('backend', $titulo) . ' ' . $box->numero . '-' . $box->name;
$this->params['breadcrumbs'][] = ['label' => 'Arqueo de Caja', 'url' => ['/cash-register/arqueo', 'box_id'=>$box->id]];
$this->params['breadcrumbs'][] = $this->title;

$create_button = '';
$add_efectivo = '';
$retirar_efectivo = '';

?>
<?php
$custom_buttons = [
    'delete' => function ($url, $model) use ($movement_type_id, $cash_register_id, $box){
        $options = [
            'title' => Yii::t('backend', 'Eliminar'),
            'class' => 'btn btn-xs btn-danger btn-flat',
            'aria-label' => Yii::t('backend', 'Eliminar'),
            'data-method' => 'post',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',
            'data-confirm' => Yii::t('backend', '¿Seguro desea eliminar este elemento?'),
        ];   
        if ($movement_type_id == MovementTypes::SALIDA_EFECTIVO)     
            return Html::a('<i class="glyphicon glyphicon-trash"></i>', ['/movement-cash-register-detail/delete-salida', 'id' => $model->id, 'cash_register_id'=>$cash_register_id, 'box_id'=>$box->id], $options);
        else            
            return Html::a('<i class="glyphicon glyphicon-trash"></i>', ['/movement-cash-register-detail/delete-entrada', 'id' => $model->id, 'cash_register_id'=>$cash_register_id, 'box_id'=>$box->id], $options);            
    },
];

$custom_elements_gridview = new Custom_Settings_Column_GridView($create_button, $dataProvider, ['delete'], $custom_buttons);
?>
<div class="box-body">
    <div class="row">
        <div class="col-md-4">
            <fieldset style="width: 100%; border: 1px solid #C0C0C0; padding-right: 15px; padding-left: 15px;">
                <legend style="width: auto; margin: 8px; border: 0; padding-right: 1%; padding-left: 1%; font-size: 16px; font-weight: bold; border: 1px solid #C0C0C0;"><?= Yii::t('backend', $titulo) ?></legend>
                <?= $this->render('_form_movimiento', [
                    'cash_register_id' => $cash_register_id,
                    'movement_type_id' => $movement_type_id,
                    'model' => $model,
                    'box' => $box
                ]) ?>
            </fieldset>
        </div>
        <div class="col-md-8">
            <p align="center"><strong>Historial de movimiento. <?= $titulo ?></strong></p>
            <?php // echo $this->render('_search', ['model' => $searchModel]); 
            ?>
            <?= GridView::widget([
                'id' => 'grid',
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
                    'position' => 'absolute',
                    'top' => 50
                ],
                'hover' => true,
                'pager' => [
                    'firstPageLabel' => Yii::t('backend', 'Primero'),
                    'lastPageLabel' => Yii::t('backend', 'Último'),
                ],
                'hover' => true,
                'persistResize' => true,
                'filterModel' => $searchModel,
                'columns' => [

                    $custom_elements_gridview->getSerialColumn(),
                    [
                        'attribute' => 'movement_date',
                        'value' => function ($data) {
                            return GlobalFunctions::formatDateToShowInSystem($data->movement_date) . ' ' . date('h:i:s A', strtotime($data->movement_time));
                        },
                        'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                        'hAlign' => 'center',
                        'filterType' => GridView::FILTER_DATE_RANGE,
                        'filterWidgetOptions' => ([
                            'model' => $searchModel,
                            'attribute' => 'movement_date',
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
                        'attribute' => 'comment',
                        'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                        'hAlign' => 'center',
                        'format' => 'html',
                        'value' => function ($data) {
                            return $data->comment;
                        }
                    ],

                    [
                        'attribute' => 'value',
                        'contentOptions' => ['class' => 'kv-align-right kv-align-middle'],
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
                                'digits' => 2
                            ],
                            'displayOptions' => ['class' => 'form-control kv-monospace'],
                            'saveInputContainer' => ['class' => 'kv-saved-cont']
                        ],
                        'value' => function ($data) {
                            return GlobalFunctions::formatNumber($data->value, 2);
                        },
                        'format' => 'html',
                    ],

                    $custom_elements_gridview->getActionColumn(),

                    $custom_elements_gridview->getCheckboxColumn(),
                ],

                'toolbar' =>  $custom_elements_gridview->getToolbar(),

                'panel' => '', // $custom_elements_gridview->getPanel(),

                'toggleDataOptions' => $custom_elements_gridview->getTogleDataOptions(),
            ]); ?>
        </div>
    </div>
</div>

<?php
$url = Url::to([$controllerId . 'multiple_delete'], GlobalFunctions::URLTYPE);
$js = Footer_Bulk_Delete::getFooterBulkDelete($url);
$this->registerJs($js, View::POS_READY);
?>