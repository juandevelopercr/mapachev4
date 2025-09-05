<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\UnitType */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Unidad de medida');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Unidades de medidas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="unit-type-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
