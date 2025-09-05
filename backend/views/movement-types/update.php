<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\MovementTypes */

$this->title = 'Actualizar Tipo de movimiento de caja: ' . $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Tipos de movimiento de caja', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="movement-types-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
