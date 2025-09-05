<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\BranchOffice */


$this->title = Yii::t('backend', 'Generar').' '. Yii::t('backend', 'Sucursal');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Sucursales'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="branch-office-create">

    <?= $this->render('_form_generate', [
        'model' => $model,
        'model_auto' => $model_auto,
    ]) ?>

</div>
