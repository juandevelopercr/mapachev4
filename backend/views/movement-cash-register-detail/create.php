<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\business\MovementCashRegisterDetail */

$this->title = 'Create Movement Cash Register Detail';
$this->params['breadcrumbs'][] = ['label' => 'Movement Cash Register Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="movement-cash-register-detail-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
