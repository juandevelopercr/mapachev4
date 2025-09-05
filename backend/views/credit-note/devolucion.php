<?php

/* @var $this yii\web\View */
/* @var $model backend\models\business\Invoice */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Devolución de Mercancias');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Notas de crédito'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="devolucion">

    <?= $this->render('_formDevolucion', [
        'model' => $model,
        'items' => $items,
    ]) ?>

</div>
