<?php

/* @var $this yii\web\View */
/* @var $model \backend\models\settings\Setting */

$this->title = Yii::t('backend', 'Configuración de correos de alerta');
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="setting-update">

    <?= $this->render('_form_alert', [
        'model' => $model,
    ]) ?>

</div>
