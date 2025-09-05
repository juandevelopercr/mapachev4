<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\MovementTypes */

$this->title = 'Crear tipo de movimiento de caja';
$this->params['breadcrumbs'][] = ['label' => 'Tipos de movimientos de caja', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="movement-types-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
