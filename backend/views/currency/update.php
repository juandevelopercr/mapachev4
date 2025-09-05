<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\Currency */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Moneda').': '. $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Monedas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="currency-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
