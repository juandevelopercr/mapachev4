<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use common\widgets\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\MovementCashRegisterSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Movement Cash Registers';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="movement-cash-register-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Movement Cash Register', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'as filterBehavior' => \thrieu\grid\FilterStateBehavior::className(),	
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'cash_register_id',
            'movement_type_id',
            'movement_date',
            'movement_time',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
