<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\BranchOffice */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Sucursal').': '. $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Sucursales'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="branch-office-update">

    <?= $this->render('_form', [
        'model' => $model,
        'modelsSector' => $modelsSector,
        'modelsSectorLocation' => $modelsSectorLocation,
    ]) ?>

</div>
