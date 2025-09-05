<?php

/* @var $this yii\web\View */
/* @var $model backend\models\business\XmlImported */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Xml Imported').': '. $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Xml Importeds'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="xml-imported-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
