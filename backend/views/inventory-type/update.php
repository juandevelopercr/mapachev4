<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\InventoryType */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Tipo de inventario').': '. $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Tipos de inventarios'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="inventory-type-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
