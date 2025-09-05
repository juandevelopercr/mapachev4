<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\RouteTransport */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Ruta de transporte');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Rutas de transporte'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="inventory-type-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
