<?php
use backend\models\nomenclators\UtilsConstants;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Adjustment */

$this->title = Yii::t('backend', 'Actualizar').' '. UtilsConstants::getAdjustmentType($model->type).': '. $model->id;
$this->params['breadcrumbs'][] = ['label' => UtilsConstants::getAdjustmentType($model->type,false,true), 'url' => [UtilsConstants::getRedirectAdjustmentType($model->type)]];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="adjustment-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
