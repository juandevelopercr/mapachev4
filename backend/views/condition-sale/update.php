<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\ConditionSale */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Condición de venta').': '. $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Condiciones de ventas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="condition-sale-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
