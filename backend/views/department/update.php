<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\Department */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Departamento').': '. $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Departamentos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="department-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
