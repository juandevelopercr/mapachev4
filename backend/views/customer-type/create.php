<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\CustomerType */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Tipo de cliente');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Tipos de clientes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-type-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
