<?php
use yii\helpers\Html;
use common\models\User;
use common\models\GlobalFunctions;
use yii\helpers\Url;
use backend\models\settings\Setting;
use backend\models\i18n\Language;
use machour\yii2\notifications\widgets\NotificationsWidget;

/* @var $this \yii\web\View */
/* @var $content string */

$return_url = Url::current();
?>

<header class="main-header">

    <?= Html::a('<span class="logo-mini"><img class="logo-header-mini" src="'.Setting::getUrlLogoBySettingAndType(3,1).'" alt=""></span><span class="logo-lg"><img class="logo-header-lg" src="'.Setting::getUrlLogoBySettingAndType(2,1).'" alt=""></span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">

                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="<?= User::getUrlAvatarByActiveUser() ?>" class="user-image" />
                        <span class="hidden-xs"><?= User::getNameByActiveUser() ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="<?= User::getUrlAvatarByActiveUser() ?>" class="img-circle"
                                 alt="User Image"/>

                            <p>
	                            <?= User::getFullNameByActiveUser() ?>
                                <small></small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <div class="pull-left">
	                            <?= Html::a(
		                            Yii::t('backend','Perfil'),
		                            ['/security/user/profile'],
		                            ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
	                            ) ?>
                            </div>
                            <div class="pull-right">
                                <?= Html::a(
                                    Yii::t('backend','Cerrar sesión'),
                                    Url::to(['/site/logout'], GlobalFunctions::URLTYPE),
                                    ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                                ) ?>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>


