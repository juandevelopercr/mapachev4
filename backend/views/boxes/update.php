<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\Boxes */

$this->title = 'Actualizar Caja: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Cajas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="boxes-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
