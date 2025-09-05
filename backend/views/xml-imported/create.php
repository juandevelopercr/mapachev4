<?php

/* @var $this yii\web\View */
/* @var $model backend\models\business\XmlImported */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Xml Imported');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Xml Importeds'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="xml-imported-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
