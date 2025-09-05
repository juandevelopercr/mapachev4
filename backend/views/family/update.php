<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\Family */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Familia').': '. $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Familias'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="family-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
