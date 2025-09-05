<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\CustomerContractSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Customer Contracts';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-contract-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Customer Contract', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'customer_id',
            'contract',
            'confirmation_number',
            'lugar_recogida',
            //'unidad_asignada',
            //'placa_unidad_asignada',
            //'fecha_recogida',
            //'fecha_devolucion',
            //'iva',
            //'porciento_descuento',
            //'naturaleza_descuento',
            //'decuento_fijo',
            //'total_comprobante',
            //'estado',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
