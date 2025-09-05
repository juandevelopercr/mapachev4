<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\UnitType */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Unidad de medida').': '. $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Unidades de medidas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="unit-type-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
