<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\TaxRateType */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Tarifa de impuesto');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Tarifas de impuestos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tax-rate-type-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
