<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\InventoryType */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Tipo de inventario');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Tipos de inventarios'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="inventory-type-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
