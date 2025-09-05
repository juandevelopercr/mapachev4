<?php
use backend\models\nomenclators\UtilsConstants;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Adjustment */

$this->title = Yii::t('backend', 'Crear').' '. UtilsConstants::getAdjustmentType($model->type);
$this->params['breadcrumbs'][] = ['label' => UtilsConstants::getAdjustmentType($model->type,false,true), 'url' => [UtilsConstants::getRedirectAdjustmentType($model->type)]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="adjustment-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
