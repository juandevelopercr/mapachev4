<?php

/* @var $this yii\web\View */
/* @var $model backend\models\business\CreditNote */
/* @var $searchModelItems \backend\models\business\ItemCreditNoteSearch */
/* @var $dataProviderItems yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Devolución de Mercancia').': '. $model->consecutive;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Notas de crédito'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->consecutive, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="invoice-update">

    <?= $this->render('_formDevolucion', [
        'model' => $model,
        'searchModelItems' => $searchModelItems,
        'dataProviderItems' => $dataProviderItems,
    ]) ?>

</div>
