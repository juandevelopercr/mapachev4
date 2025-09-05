<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\TaxRateType */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Tarifa de impuesto').': '. $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Tarifas de impuestos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="tax-rate-type-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
