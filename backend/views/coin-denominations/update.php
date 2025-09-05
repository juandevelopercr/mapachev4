<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\CoinDenominations */

$this->title = 'Actualizar Denominación de moneda: ' . $model->description;
$this->params['breadcrumbs'][] = ['label' => 'Coin Denominations', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="coin-denominations-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
