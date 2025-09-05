<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\business\MovementCashRegisterDetail */

$this->title = 'Update Movement Cash Register Detail: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Movement Cash Register Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="movement-cash-register-detail-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
