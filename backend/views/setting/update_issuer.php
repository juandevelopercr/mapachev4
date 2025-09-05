<?php

/* @var $this yii\web\View */
/* @var $model \backend\models\settings\Setting */

$this->title = Yii::t('backend', 'Configuración de emisor');
$this->params['breadcrumbs'][] = Yii::t('yii', 'Update');
?>
<div class="setting-update">

    <?= $this->render('_form_issuer', [
        'model' => $model,
    ]) ?>

</div>
