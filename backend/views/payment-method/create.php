<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\Project */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Proyecto');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Proyectos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
