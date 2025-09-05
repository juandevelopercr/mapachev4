<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\business\CustomerContract */

$this->title = 'Create Customer Contract';
$this->params['breadcrumbs'][] = ['label' => 'Customer Contracts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-contract-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
