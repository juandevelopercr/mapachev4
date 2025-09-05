<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\business\CustomerContract */

$this->title = 'Update Customer Contract: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Customer Contracts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="customer-contract-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
