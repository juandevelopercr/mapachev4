<?php

/* @var $this yii\web\View */
/* @var $model backend\models\business\DebitNote */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Nota de débito');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Notas de débito'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="proforma-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
