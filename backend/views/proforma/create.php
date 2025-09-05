<?php

/* @var $this yii\web\View */
/* @var $model backend\models\business\Proforma */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Proforma');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Proformas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="proforma-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
