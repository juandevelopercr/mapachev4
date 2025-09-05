<?php

/* @var $this yii\web\View */
/* @var $model backend\models\business\DebitNote */
/* @var $searchModelItems \backend\models\business\ItemDebitNoteSearch */
/* @var $dataProviderItems yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Nota de débito').': '. $model->consecutive;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Notas de débito'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->consecutive, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="invoice-update">

    <?= $this->render('_form', [
        'model' => $model,
        'searchModelItems' => $searchModelItems,
        'dataProviderItems' => $dataProviderItems,
    ]) ?>

</div>
