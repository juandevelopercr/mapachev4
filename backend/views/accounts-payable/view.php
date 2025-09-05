<?php

use yii\helpers\Html;
use common\models\User;
use yii\widgets\ListView;
use common\widgets\DetailView;
use mdm\admin\components\Helper;
use common\models\GlobalFunctions;
use backend\models\business\Invoice;
use backend\models\business\Documents;
use backend\models\business\InvoiceAbonos;
use backend\models\nomenclators\RouteTransport;
use backend\models\nomenclators\UtilsConstants;
use backend\models\business\CollectorHasInvoice;
use backend\models\business\PaymentMethodHasInvoice;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Invoice */

$controllerId = '/' . $this->context->uniqueId . '/';
$this->title = $model->key;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Cuentas por Pagar'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="box-header">
    <?php
    if (Helper::checkRoute($controllerId . 'update')) {
        echo Html::a('<i class="fa fa-pencil"></i> ' . Yii::t('yii', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-default btn-flat margin']);
    }

    echo Html::a('<i class="fa fa-remove"></i> ' . Yii::t('backend', 'Cancelar'), ['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Cancelar')]);

    if (Helper::checkRoute($controllerId . 'delete')) {
        echo Html::a('<i class="fa fa-trash"></i> ' . Yii::t('yii', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger btn-flat margin',
            'data' => [
                'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]);
    }

    $color = '';
    $status = (int) $model->status;

    if ($status === UtilsConstants::ACCOUNT_PAYABLE_PENDING) {
        $color = 'red';
    } elseif ($status === UtilsConstants::ACCOUNT_PAYABLE_CANCELLED) {
        $color = 'blue';
    }

    $label_status_hacienda = " <small class=\"badge bg-" . $color . "\"></i> " . UtilsConstants::getHaciendaStatusSelectType($status, true) . "</small>";

    ?>
</div>
<div class="box-body">

    <div class="row">
        <div class="col-md-6">
            <?= DetailView::widget([
                'model' => $model,
                'labelColOptions' => ['style' => 'width: 40%'],
                'attributes' => [

                    'key',
                    [
                        'attribute' => 'emission_date',
                        'value' => GlobalFunctions::formatDateToShowInSystem($model->emission_date),
                        'format' => 'html',
                    ],
                    [
                        'attribute' => 'proveedor',
                        'value' =>$model->getProveedor($model),       
                        'format' => 'html',
                    ],
                    'currency',                                        
                    [
                        'attribute' => 'total_invoice',
                        'value' => GlobalFunctions::formatNumber($model->total_invoice, 2),
                        'format' => 'html',
                    ],
                    [
                        'attribute' => 'status',
                        'value' => UtilsConstants::getStatusAccountsPayable($model->status),
                        'format' => 'html',
                    ],
                ],
            ]) ?>
        </div>        
    </div>
  

    <div class="row">
        <div class="col-md-9">
            <?= GlobalFunctions::beginCustomPanel(count($model->abonos) . ' ' . Yii::t('backend', 'Abonos'), false, 'box box-danger box-solid') ?>

            <div class="row">
                <div class="col-md-12">            
                    <?= ListView::widget([
                        'dataProvider' => $dataProviderAbonos,
                        'options' => ['class' => 'row'],
                        'itemOptions' => ['class' => 'col-md-12'],
                        'itemView' => '_items_abonos',
                        'summary' => false,
                        'emptyText' => Yii::t('backend', 'No hay abonos asociados a esta factura'),
                        'emptyTextOptions' => ['class' => 'text-no-items']
                    ]);
                    ?>
                </div>
            </div>          

            <div class="row">
                <div class="pull-right" style="padding-right: 20px;">  
                    <table cellpadding="5" cellspacing="5" width="250px" align="right" border="0">
                        <tr>
                            <td width="100px" align="right">
                                <strong>Monto</strong>
                            </td>
                            <td width="150px" align="right">
                                <strong><?= $model->currency ?> <?= GlobalFunctions::formatNumber($total, 2) ?></strong>
                            </td>                            
                        </tr>
                        <tr>
                            <td align="right">
                                <strong>Total Abonos</strong>
                            </td>
                            <td align="right">
                                <strong><?= $model->currency ?> <?= GlobalFunctions::formatNumber($abonado, 2) ?></strong>
                            </td>                            
                        </tr>  
                        <tr>
                            <td align="right">
                                <strong>Saldo</strong>
                            </td>
                            <td align="right">
                                <strong><?= $model->currency ?> <?= GlobalFunctions::formatNumber($pendiente, 2) ?></strong>
                            </td>                            
                        </tr>                                               
                    </table>
                </div>
            </div>    

            <?= GlobalFunctions::endCustomPanel() ?>

        </div>
        <div class="col-md-3">
            <?php
            /*
            <?= GlobalFunctions::beginCustomPanel(Yii::t('backend', 'Resumen')) ?>
            <?php
            $resume = Invoice::getResumeInvoice($model->id);
            ?>
            <div class="row">
                <div class="col-md-12 custom-padding-5">
                    <?= '<b>' . Yii::t('backend', 'Descuento') . ': </b>' . GlobalFunctions::formatNumber($resume->discount_amount, 2) ?>
                </div>
                <div class="col-md-12 custom-padding-5">
                    <?= '<b>' . Yii::t('backend', 'Subtotal') . ': </b>' . GlobalFunctions::formatNumber($resume->subtotal, 2) ?>
                </div>
                <div class="col-md-12 custom-padding-5">
                    <?= '<b>' . Yii::t('backend', 'IVA') . ': </b>' . GlobalFunctions::formatNumber($resume->tax_amount, 2) ?>
                </div>
                <div class="col-md-12 custom-padding-5">
                    <?= '<b>' . Yii::t('backend', 'Exoneración') . ': </b>' . GlobalFunctions::formatNumber($resume->exonerate_amount, 2) ?>
                </div>
                <div class="col-md-12 custom-padding-5">
                    <?= '<b>' . Yii::t('backend', 'Total') . ': </b>' . GlobalFunctions::formatNumber($resume->price_total, 2) ?>
                </div>
            </div>
            <?= GlobalFunctions::endCustomPanel() ?>
            */
            ?>
        </div>
    </div>         
</div>