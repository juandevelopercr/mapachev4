<?php

/* @var $this yii\web\View */
/* @var $model backend\models\business\Proforma */
/* @var $searchModelItems \backend\models\business\ItemProformaSearch */
/* @var $dataProviderItems yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Proforma').': '. $model->consecutive;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Proformas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->consecutive, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="proforma-update">

    <?= $this->render('_form', [
        'model' => $model,
        'searchModelItems' => $searchModelItems,
        'dataProviderItems' => $dataProviderItems,
    ]) ?>

</div>
