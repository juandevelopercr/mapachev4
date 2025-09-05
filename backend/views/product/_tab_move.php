<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use common\models\GlobalFunctions;
use common\models\User;
use backend\models\nomenclators\UtilsConstants;
use backend\models\business\Invoice;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Product */
/* @var $searchModel backend\models\business\AdjustmentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>

<div class="row">

    <div class="col-md-12">
        <?= GridView::widget([
            'id' => 'grid',
            'summary' => false,
            'dataProvider' => $dataProvider,
            'pjax' => true,
            'pjaxSettings' => [
                'neverTimeout' => true,
                'options' => [
                    'enablePushState' => false,
                    'scrollTo' => false,
                ],
            ],
            'autoXlFormat' => true,
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
            'columns' => [

                [
                    'class' => 'kartik\grid\SerialColumn',
                    'contentOptions' => ['class' => 'kartik-sheet-style'],
                    'width' => '36px',
                    'header' => '#',
                    'headerOptions' => ['class' => 'kartik-sheet-style']
                ],

                [
                    'attribute' => 'type',
                    'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                    'format' => 'html',
                    'value' => function ($data) {
                        return UtilsConstants::getAdjustmentType($data->type);
                    }
                ],

                [
                    'attribute' => 'consecutive',
                    'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                    'format' => 'html',
                    'value' => function ($data) {
                        return $data->consecutive;
                    }
                ],

                [
                    'attribute' => 'past_quantity',
                    'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                    'value' => function ($data) {
                        $type = (int) $data->type;
                        return ($type !== UtilsConstants::ADJUSTMENT_TYPE_TRANFER) ? GlobalFunctions::formatNumber($data->past_quantity, 2) : 'N/A';
                    },
                    'format' => 'html',
                ],

                [
                    'attribute' => 'entry_quantity',
                    'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                    'value' => function ($data) {
                        return GlobalFunctions::formatNumber($data->entry_quantity, 2);
                    },
                    'format' => 'html',
                ],

                [
                    'attribute' => 'new_quantity',
                    'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                    'value' => function ($data) {
                        $type = (int) $data->type;
                        return ($type !== UtilsConstants::ADJUSTMENT_TYPE_TRANFER) ? GlobalFunctions::formatNumber($data->new_quantity, 2) : 'N/A';
                    },
                    'format' => 'html',
                ],

                [
                    'attribute' => 'origin_branch_office_id',
                    'format' => 'html',
                    'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                    'value' => function ($model) {
                        return $model->originBranchOffice->code . ' - ' . $model->originBranchOffice->name;
                    },
                ],

                [
                    'attribute' => 'target_branch_office_id',
                    'label' => Yii::t('backend', 'Sucursal destino'),
                    'format' => 'html',
                    'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                    'value' => function ($model) {
                        return (isset($model->target_branch_office_id)) ? $model->targetBranchOffice->code . ' - ' . $model->targetBranchOffice->name : 'N/A';
                    },
                ],

                [
                    'attribute' => 'invoice_number',
                    'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                    'format' => 'raw',
                    'value' => function ($data) {
                        if (isset($data->invoice_number) && !empty($data->invoice_number) && strlen($data->invoice_number) <= 11) {
                            $invoice = Invoice::find()->select(['id', 'consecutive'])->where(['id' => (int)$data->invoice_number])->one();
                            if ($invoice !== null) {
                                return Html::a($invoice->consecutive, ['invoice/view', 'id' => $data->invoice_number], ['target' => '_blank']);
                            }
                        } else {
                            return 'N/A';
                        }
                    }
                ],

                [
                    'attribute' => 'created_at',
                    'format' => 'html',
                    'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                    'value' => function ($model) {
                        return GlobalFunctions::formatDateToShowInSystem($model->created_at);
                    },
                ],

                [
                    'attribute' => 'observations',
                    'headerOptions' => ['class' => 'custom_width'],
                    'contentOptions' => ['class' => 'custom_width'],
                    'hAlign' => 'center',
                    'format' => 'html',
                    'value' => function ($data) {
                        return isset($data->observations) ? $data->observations : '';
                    }
                ],

                [
                    'attribute' => 'user_id',
                    'format' => 'html',
                    'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                    'value' => function ($model) {
                        return User::getFullNameByUserId($model->user_id);
                    },
                ],

            ],
        ]);
        ?>
    </div>

</div>