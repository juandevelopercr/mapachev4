<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\TaxType */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Tipo de impuesto').': '. $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Tipos de impuestos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="tax-type-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
