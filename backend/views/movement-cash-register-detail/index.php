<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use common\widgets\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\MovementCashRegisterDetailSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Movement Cash Register Details';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="movement-cash-register-detail-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Movement Cash Register Detail', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'as filterBehavior' => \thrieu\grid\FilterStateBehavior::className(),	
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'movement_cash_register_id',
            'value',
            'count',
            'comment',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
