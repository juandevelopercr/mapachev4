<?php

/* @var $this yii\web\View */
/* @var $user common\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['/security/user/reset-password', 'token' => $user->password_reset_token]);
?>

<?= Yii::t('common','Atención') ?>,
<?= Yii::t('common','Utilice el siguiente código para restablecer su contraseña') ?>: <?= $user->getCodeToken() ?>
