<?php

use yii\helpers\Html;
use common\widgets\DetailView;
use mdm\admin\components\Helper;
use common\models\GlobalFunctions;
use yii\widgets\ListView;
use backend\models\business\Proforma;
use backend\models\nomenclators\UtilsConstants;
use backend\models\business\PaymentMethodHasProforma;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Proforma */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = $model->consecutive;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Proformas'), 'url' => ['index']];
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
                        'consecutive',

                        [
                            'attribute'=> 'request_date',
                            'value'=> GlobalFunctions::formatDateToShowInSystem($model->request_date),
                            'format'=> 'html',
                        ],

                        [
                            'attribute'=> 'branch_office_id',
                            'value'=> (isset($model->branchOffice->name) && !empty($model->branchOffice->name))? $model->branchOffice->name : null,
                            'format'=> 'html',
                        ],

                        [
                            'attribute'=> 'customer_id',
                            'value'=> (isset($model->customer->name) && !empty($model->customer->name))? $model->customer->name : null,
                            'format'=> 'html',
                        ],

                        [
                            'attribute'=> 'seller_id',
                            'value'=> (isset($model->seller_id) && !empty($model->seller_id))? User::getFullNameByUserId($model->seller_id) : null,
                            'format'=> 'html',
                        ],

                        'delivery_time',
                        [
                            'attribute'=> 'delivery_time_type',
                            'value'=> UtilsConstants::getDeliveryTimesSelectType($model->delivery_time_type),
                            'format'=> 'html',
                        ],

                        [
                            'attribute'=> 'status',
                            'value'=> UtilsConstants::getProformaStatusSelectType($model->status),
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
                            'attribute'=> 'condition_sale_id',
                            'value'=> (isset($model->conditionSale->name) && !empty($model->conditionSale->name))? $model->conditionSale->name : null,
                            'format'=> 'html',
                        ],

                        [
                            'attribute'=> 'credit_days_id',
                            'value'=> (isset($model->creditDays->name) && !empty($model->creditDays->name))? $model->creditDays->name : null,
                            'format'=> 'html',
                        ],

                        [
                            'attribute'=> 'currency_id',
                            'value'=> (isset($model->currency->name) && !empty($model->currency->name))? $model->currency->name : null,
                            'format'=> 'html',
                        ],


                        [
                            'attribute'=> 'change_type',
                            'value'=> GlobalFunctions::formatNumber($model->change_type,2),
                            'format'=> 'html',
                        ],

                        [
                            'label'=> Yii::t('backend','Medios de pagos'),
                            'value'=> PaymentMethodHasProforma::getPaymentMethodStringByProforma($model->id),
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
        <div class="row">
            <div class="col-md-9">
                <?= GlobalFunctions::beginCustomPanel(count($model->itemProformas). ' '.Yii::t('backend','Items')) ?>
                <?= ListView::widget([
                    'dataProvider' => $dataProviderItems,
                    'options' => ['class' => 'row'],
                    'itemOptions' => ['class' => 'col-md-12'],
                    'itemView' => '_items',
                    'summary' => false,
                    'emptyText' => Yii::t('backend','No hay items asociados a esta proforma'),
                    'emptyTextOptions' => ['class' => 'text-no-items']
                ]);
                ?>
                <?= GlobalFunctions::endCustomPanel() ?>
            </div>
            <div class="col-md-3">
                <?= GlobalFunctions::beginCustomPanel(Yii::t('backend','Resumen')) ?>
                <?php
                $resume = Proforma::getResumeProforma($model->id);
                ?>
                <div class="row">
                    <div class="col-md-12 custom-padding-5">
                        <?= '<b>'.Yii::t('backend','Descuento').': </b>'.GlobalFunctions::formatNumber($resume->discount_amount,2) ?>
                    </div>
                    <div class="col-md-12 custom-padding-5">
                        <?= '<b>'.Yii::t('backend','Subtotal').': </b>'.GlobalFunctions::formatNumber($resume->subtotal,2) ?>
                    </div>
                    <div class="col-md-12 custom-padding-5">
                        <?= '<b>'.Yii::t('backend','IVA').': </b>'.GlobalFunctions::formatNumber($resume->tax_amount,2) ?>
                    </div>
                    <div class="col-md-12 custom-padding-5">
                        <?= '<b>'.Yii::t('backend','Exoneración').': </b>'.GlobalFunctions::formatNumber($resume->exonerate_amount,2) ?>
                    </div>
                    <div class="col-md-12 custom-padding-5">
                        <?= '<b>'.Yii::t('backend','Total').': </b>'.GlobalFunctions::formatNumber($resume->price_total,2) ?>
                    </div>
                </div>
                <?= GlobalFunctions::endCustomPanel() ?>
            </div>
        </div>

    </div>
