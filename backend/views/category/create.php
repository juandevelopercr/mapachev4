<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\Category */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Categoría');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Categorías'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
