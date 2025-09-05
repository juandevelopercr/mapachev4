<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\ConditionSale */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Condición de venta');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Condiciones de ventas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="condition-sale-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
