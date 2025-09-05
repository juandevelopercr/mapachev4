<?php

/* @var $this yii\web\View */
/* @var $model backend\models\business\Invoice */
/* @var $searchModelItems \backend\models\business\ItemInvoiceSearch */
/* @var $dataProviderItems yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Cuenta por Pagar').': '. $model->key;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Cuentas por Pagar'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->key, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="cuentas-por-pagar-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
