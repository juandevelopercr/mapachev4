<?php

/* @var $this yii\web\View */
/* @var $model backend\models\business\Invoice */
/* @var $searchModelItems \backend\models\business\ItemInvoiceSearch */
/* @var $dataProviderItems yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Corrección de factura');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Facturas y tiquetes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Corregir');
?>
<div class="invoice-update">

    <?= $this->render('_form_correction', [
        'model' => $model,
        'searchModelItems' => $searchModelItems,
        'dataProviderItems' => $dataProviderItems,
    ]) ?>

</div>
