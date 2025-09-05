<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\CoinDenominations */

$this->title = 'Crear denominación de moneda';
$this->params['breadcrumbs'][] = ['label' => 'Denominaciones de monedas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="coin-denominations-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
