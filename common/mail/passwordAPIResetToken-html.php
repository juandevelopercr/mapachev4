<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user \common\models\User */

?>
<div class="password-reset">
    <p><b><?= Yii::t('common','Atención') ?>:</b></p>

    <p><?= Yii::t('common','Utilice el código de abajo para restablecer su contraseña') ?>:</p>

    <p><?= $user->getCodeToken() ?></p>
</div>
