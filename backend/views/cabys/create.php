<?php

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\Cabys */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Cabys');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Cabys'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cabys-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
