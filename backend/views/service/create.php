<?php

/* @var $this yii\web\View */
/* @var $model backend\models\business\Service */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Servicio');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Servicios'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
