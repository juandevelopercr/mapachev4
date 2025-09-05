<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\Department */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Departamento');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Departamentos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="department-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
