<?php

/* @var $this yii\web\View */
/* @var $model \common\models\User */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Agente');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Agentes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="collector-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
