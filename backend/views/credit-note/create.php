<?php

/* @var $this yii\web\View */
/* @var $model backend\models\business\Invoice */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Nota de crédito');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Notas de crédito'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="proforma-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
