<?php

use yii\helpers\Html;
use common\widgets\DetailView;
use mdm\admin\components\Helper;
use common\models\GlobalFunctions;
use backend\models\nomenclators\Cabys;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\TaxType;
use backend\models\nomenclators\TaxRateType;
use backend\models\nomenclators\ExonerationDocumentType;
use yii\widgets\ListView;
use kartik\tabs\TabsX;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Service */

$controllerId = '/' . $this->context->uniqueId . '/';
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Servicios'), 'url' => ['index']];
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
    ?>
</div>
<div class="box-body">

    <?php
    $total = $model->price - $model->discount_amount + $model->tax_amount;

    $main_data = DetailView::widget([
        'model' => $model,
        'labelColOptions' => ['style' => 'width: 40%'],
        'attributes' => [
            'id',
            'code',
            [
                'attribute' => 'cabys_id',
                'value' => (isset($model->cabys->code) && !empty($model->cabys->code)) ? $model->cabys->code . ' - ' . $model->cabys->description_service . ' - ' . $model->cabys->tax : null,
                'format' => 'html',
            ],

            'name',
            [
                'attribute' => 'unit_type_id',
                'value' => (isset($model->unitType->name) && !empty($model->unitType->name)) ? $model->unitType->code . ' - ' . $model->unitType->name : null,
                'format' => 'html',
            ],

            [
                'attribute' => 'created_at',
                'value' => GlobalFunctions::formatDateToShowInSystem($model->created_at),
                'format' => 'html',
            ],
        ],
    ]);
    $price_data = DetailView::widget([
        'model' => $model,
        'labelColOptions' => ['style' => 'width: 40%'],
        'attributes' => [

            [
                'attribute' => 'price',
                'value' => GlobalFunctions::formatNumber($model->price, 2),
                'format' => 'html',
            ],

            [
                'attribute' => 'discount_amount',
                'value' => GlobalFunctions::formatNumber($model->discount_amount, 2),
                'format' => 'html',
            ],

            'nature_discount',
            [
                'attribute' => 'tax_type_id',
                'value' => (isset($model->taxType->name) && !empty($model->taxType->name)) ? $model->taxType->code . ' - ' . $model->taxType->name : null,
                'format' => 'html',
            ],

            [
                'attribute' => 'tax_rate_type_id',
                'value' => (isset($model->taxRateType->name) && !empty($model->taxRateType->name)) ? $model->taxRateType->code . ' - ' . $model->taxRateType->name : null,
                'format' => 'html',
            ],

            [
                'attribute' => 'tax_rate_percent',
                'value' => GlobalFunctions::formatNumber($model->tax_rate_percent, 2),
                'format' => 'html',
            ],

            [
                'attribute' => 'tax_amount',
                'value' => GlobalFunctions::formatNumber($model->tax_amount, 2),
                'format' => 'html',
            ],

            [
                'label' => Yii::t('backend', 'Precio final'),
                'value' => GlobalFunctions::formatNumber($total, 2),
                'format' => 'html',
            ],
        ],
    ]);

    $exonerate_data = DetailView::widget([
        'model' => $model,
        'labelColOptions' => ['style' => 'width: 40%'],
        'attributes' => [

            [
                'attribute' => 'exoneration_document_type_id',
                'value' => (isset($model->exonerationDocumentType->name) && !empty($model->exonerationDocumentType->name)) ? $model->exonerationDocumentType->code . ' - ' . $model->exonerationDocumentType->name : null,
                'format' => 'html',
            ],

            'number_exoneration_doc',
            'name_institution_exoneration',
            [
                'attribute' => 'exoneration_date',
                'value' => GlobalFunctions::formatDateToShowInSystem($model->exoneration_date),
                'format' => 'html',
            ],

            [
                'attribute' => 'exoneration_purchase_percent',
                'value' => GlobalFunctions::formatNumber($model->exoneration_purchase_percent, 2),
                'format' => 'html',
            ],

            [
                'attribute' => 'exonerated_tax_amount',
                'value' => GlobalFunctions::formatNumber($model->exonerated_tax_amount, 2),
                'format' => 'html',
            ],
        ],
    ]);

    echo TabsX::widget([
        'position' => TabsX::POS_ABOVE,
        'encodeLabels' => false,
        'items' => [
            [
                'label' => '<i class="glyphicon glyphicon-list-alt"></i> ' . Yii::t('backend', 'Datos Generales'),
                'content' => $main_data,
                'active' => true
            ],

            [
                'label' => '<i class="fa fa-money"></i> ' . Yii::t('backend', 'Datos venta'),
                'content' => $price_data,
                'active' => false
            ],

            [
                'label' => '<i class="fa fa-asterisk"></i> ' . Yii::t('backend', 'Datos exoneración'),
                'content' => $exonerate_data,
                'active' => false
            ],
        ],
    ]);
    ?>
</div>