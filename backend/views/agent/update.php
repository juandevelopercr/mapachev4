<?php

/* @var $this yii\web\View */
/* @var $model \common\models\User */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Agente').': '. $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Agentes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="collector-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
