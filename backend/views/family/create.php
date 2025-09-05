<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\Family */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Familia');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Familias'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="family-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
