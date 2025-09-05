<?php

use yii\helpers\Html;
use common\widgets\DetailView;
use mdm\admin\components\Helper;
use common\models\GlobalFunctions;
use backend\models\business\Supplier;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\UtilsConstants;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Entry */
/* @var $total_items integer */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Entradas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="box-header">
        <?php 
        if (Helper::checkRoute($controllerId . 'update')) {
            echo Html::a('<i class="fa fa-pencil"></i> '.Yii::t('yii','Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-default btn-flat margin']);
        }

        echo Html::a('<i class="fa fa-remove"></i> '.Yii::t('backend','Cancelar'), ['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Cancelar')]);

        if (Helper::checkRoute($controllerId . 'delete')) {
            echo Html::a('<i class="fa fa-trash"></i> '.Yii::t('yii','Delete'), ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger btn-flat margin',
                'data' => [
                    'confirm' => Yii::t('yii','Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]);
        }
        ?>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <?= DetailView::widget([
                    'model' => $model,
                    'labelColOptions' => ['style' => 'width: 40%'],
                    'attributes' => [

                        'order_purchase',

                        [
                            'attribute'=> 'invoice_date',
                            'value'=> GlobalFunctions::formatDateToShowInSystem($model->invoice_date),
                            'format'=> 'html',
                        ],

                        'invoice_number',
                        [
                            'attribute'=> 'invoice_type',
                            'value'=> UtilsConstants::getInvoiceType($model->invoice_type),
                            'format'=> 'html',
                        ],

                    ],
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= DetailView::widget([
                    'model' => $model,
                    'labelColOptions' => ['style' => 'width: 40%'],
                    'attributes' => [
                        [
                            'attribute'=> 'amount',
                            'value'=> GlobalFunctions::formatNumber($model->amount,2),
                            'format'=> 'html',
                        ],

                        [
                            'attribute'=> 'supplier_id',
                            'value'=> (isset($model->supplier->name) && !empty($model->supplier->name))? $model->supplier->code.' - '.$model->supplier->name : null,
                            'format'=> 'html',
                        ],

                        [
                            'attribute'=> 'branch_office_id',
                            'value'=> (isset($model->branchOffice->name) && !empty($model->branchOffice->name))? $model->branchOffice->code.' - '.$model->branchOffice->name : null,
                            'format'=> 'html',
                        ],

                        [
                            'attribute'=> 'observations',
                            'value'=> $model->observations,
                            'format'=> 'html',
                        ],

                    ],
                ]) ?>
            </div>
        </div>
        <?= GlobalFunctions::beginCustomPanel($total_items. ' '.Yii::t('backend','Items')) ?>
        <?= ListView::widget([
            'dataProvider' => $dataProviderItemEntry,
            'options' => ['class' => 'row'],
            'itemOptions' => ['class' => 'col-md-12'],
            'itemView' => '_items',
            'summary' => false,
            'emptyText' => Yii::t('backend','No hay items asociados a esta entrada'),
            'emptyTextOptions' => ['class' => 'text-no-items']
        ]);
        ?>
        <?= GlobalFunctions::endCustomPanel() ?>


    </div>
