<?php

/* @var $this yii\web\View */
/* @var $model backend\models\business\Invoice */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Factura');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Facturas y tiquetes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="proforma-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
