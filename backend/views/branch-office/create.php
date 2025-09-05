<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\BranchOffice */


$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Sucursal');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Sucursales'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="branch-office-create">

    <?= $this->render('_form', [
        'model' => $model,
        'modelsSector' => $modelsSector,
        'modelsSectorLocation' => $modelsSectorLocation,
    ]) ?>

</div>
