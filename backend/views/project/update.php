<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\Project */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Proyecto').': '. $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Proyectos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="project-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
