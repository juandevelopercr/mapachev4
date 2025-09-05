<?php

/* @var $this yii\web\View */
/* @var $model backend\models\business\PurchaseOrder */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Orden de pedido');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Órdenes de pedido'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="proforma-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
