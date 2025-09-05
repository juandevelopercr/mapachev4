<?php

/* @var $this yii\web\View */
/* @var $model backend\models\business\Invoice */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Gasto Manual');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Gastos Manuales'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="gastos-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
