<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\Cabys */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Cabys').': '. $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Cabys'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="cabys-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
