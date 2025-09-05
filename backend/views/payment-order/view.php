<?php

use yii\helpers\Html;
use common\widgets\DetailView;
use mdm\admin\components\Helper;
use common\models\GlobalFunctions;
use backend\models\nomenclators\UtilsConstants;
use yii\widgets\ListView;
use backend\models\business\PaymentOrder;
use backend\models\business\PaymentMethodHasPaymentOrder;

/* @var $this yii\web\View */
/* @var $model backend\models\business\PaymentOrder */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = $model->number;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Órdenes de compra'), 'url' => ['index']];
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

        echo '<span class="kv-grid-toolbar">
							<div class="btn-group">
							  <button class="btn btn-default dropdown-toggle margin" title="Orden de Compra" data-toggle="dropdown"><i class="glyphicon glyphicon-print"></i> Orden de Compra <span class="caret"></span></button>
							  <ul class="dropdown-menu dropdown-menu-right">
								<li title="Pdf Orden de Compra Colones">'.
            Html::a("<i class='fa fa-file-pdf-o'></i> Orden de Compra ¢ Original",['/payment-order/viewpdfcolonesoriginal','id' => $model->id], ['class'=>'btn-pdf-colones-original', 'target' => '_blank'])
            .'
								</li>
								<li title="Pdf Orden de Compra Colones">'.
            Html::a("<i class='fa fa-file-pdf-o'></i> Orden de Compra ¢ Copia",['/payment-order/viewpdfcolonescopia','id' => $model->id], ['class'=>'btn-pdf-colones-copia', 'target' => '_blank'])
            .'
								</li>								
								<li title="Pdf Orden de Compra Dolar">'.
            Html::a("<i class='fa fa-file-pdf-o'></i> Orden de Compra $ Original",['/payment-order/viewpdfdolaroriginal','id' => $model->id], ['class'=>'btn-pdf-dolar-original', 'target' => '_blank'])
            .'
								</li>
								<li title="Pdf Orden de Compra Dolar">'.
            Html::a("<i class='fa fa-file-pdf-o'></i> Orden de Compra $ Copia",['/payment-order/viewpdfdolarcopia','id' => $model->id], ['class'=>'btn-pdf-dolar-copia', 'target' => '_blank'])
            .'
								</li>								
							  </ul>
							</div>					
					</span>';
        ?>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <?= DetailView::widget([
                    'model' => $model,
                    'labelColOptions' => ['style' => 'width: 40%'],
                    'attributes' => [
                        'number',
                        [
                            'attribute'=> 'request_date',
                            'value'=> GlobalFunctions::formatDateToShowInSystem($model->request_date),
                            'format'=> 'html',
                        ],

                        [
                            'attribute'=> 'require_date',
                            'value'=> GlobalFunctions::formatDateToShowInSystem($model->require_date),
                            'format'=> 'html',
                        ],

                        [
                            'attribute'=> 'supplier_id',
                            'value'=> (!is_null($model->supplier))? $model->supplier->code.' - '.$model->supplier->name : null,
                            'format'=> 'html',
                        ],

                        [
                            'attribute'=> 'status_payment_order_id',
                            'value'=> (isset($model->status_payment_order_id) && !empty($model->status_payment_order_id))? UtilsConstants::getStatusPaymentOrderSelectMap($model->status_payment_order_id,false,true) : null,
                            'format'=> 'html',
                        ],

                        [
                            'attribute'=> 'payout_status',
                            'value'=> (isset($model->payout_status) && !empty($model->payout_status))? UtilsConstants::getPayoutStatusSelectType($model->payout_status) : null,
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
                            'value'=> (isset($model->condition_sale_id) && !empty($model->condition_sale_id))? $model->conditionSale->code.' - '.$model->conditionSale->name : null,
                            'format'=> 'html',
                        ],

                        [
                            'attribute'=> 'credit_days_id',
                            'value'=> (isset($model->credit_days_id) && !empty($model->credit_days_id))? $model->creditDays->name : null,
                            'format'=> 'html',
                        ],

                        [
                            'attribute'=> 'currency_id',
                            'value'=> (isset($model->currency_id) && !empty($model->currency_id))? $model->currency->symbol.' - '.$model->currency->name : null,
                            'format'=> 'html',
                        ],

                        [
                            'attribute'=> 'change_type',
                            'value'=> GlobalFunctions::formatNumber($model->change_type,2),
                            'format'=> 'html',
                        ],

                        [
                            'label'=> Yii::t('backend','Medios de pagos'),
                            'value'=> PaymentMethodHasPaymentOrder::getPaymentMethodStringByPaymentOrder($model->id),
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
                <?= GlobalFunctions::beginCustomPanel(count($model->itemPaymentOrders). ' '.Yii::t('backend','Items')) ?>
                <?= ListView::widget([
                    'dataProvider' => $dataProviderItems,
                    'options' => ['class' => 'row'],
                    'itemOptions' => ['class' => 'col-md-12'],
                    'itemView' => '_items',
                    'summary' => false,
                    'emptyText' => Yii::t('backend','No hay items asociados a esta orden de compra'),
                    'emptyTextOptions' => ['class' => 'text-no-items']
                ]);
                ?>
                <?= GlobalFunctions::endCustomPanel() ?>
            </div>
            <div class="col-md-3">
                <?= GlobalFunctions::beginCustomPanel(Yii::t('backend','Resumen')) ?>
                <?php
                    $resume = PaymentOrder::getResumePaymentOrder($model->id);
                ?>
                    <div class="row">
                        <div class="col-md-12 custom-padding-5">
                            <?= '<b>'.Yii::t('backend','Subtotal').': </b>'.GlobalFunctions::formatNumber($resume->subtotal,2) ?>
                        </div>
                        <div class="col-md-12 custom-padding-5">
                            <?= '<b>'.Yii::t('backend','Descuento').': </b>'.GlobalFunctions::formatNumber($resume->discount_amount,2) ?>
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