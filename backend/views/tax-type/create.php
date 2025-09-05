<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\TaxType */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Tipo de impuesto');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Tipos de impuestos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tax-type-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
