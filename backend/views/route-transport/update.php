<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\RouteTransport */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Ruta de transporte').': '. $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Rutas de transporte'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="inventory-type-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
