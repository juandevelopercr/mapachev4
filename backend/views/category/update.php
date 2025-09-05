<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\Category */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Categoría').': '. $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Categorías'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="category-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
