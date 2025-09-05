<?php

/* @var $this yii\web\View */
/* @var $model backend\models\business\Service */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Servicio').': '. $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Servicios'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="service-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
