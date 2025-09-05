<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\Currency */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Moneda');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Monedas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="currency-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
