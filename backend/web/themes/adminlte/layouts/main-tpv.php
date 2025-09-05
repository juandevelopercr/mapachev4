<?php
use common\models\GlobalFunctions;
use yii\helpers\Html;
use yii\helpers\Url;


/* @var $this \yii\web\View */
/* @var $content string */

if (Yii::$app->controller->action->id === 'login') { 
/**
 * Do not use this code in your template. Remove it. 
 * Instead, use the code  $this->layout = '//main-login'; in your controller.
 */
    echo $this->render(
        'main-login',
        ['content' => $content]
    );
}
elseif (Yii::$app->controller->action->id === 'request-password-reset') {
    /**
     * Do not use this code in your template. Remove it.
     * Instead, use the code  $this->layout = '//main-login'; in your controller.
     */
    echo $this->render(
        'main-reset',
        ['content' => $content]
    );
}
elseif (Yii::$app->controller->action->id === 'reset-password') {
    /**
     * Do not use this code in your template. Remove it.
     * Instead, use the code  $this->layout = '//main-login'; in your controller.
     */
    echo $this->render(
        'main-reset',
        ['content' => $content]
    );
}
else {
    /*
    if (class_exists('backend\assets\AppAsset')) {
        backend\assets\AppAsset::register($this);
    } else {
        backend\assets\AppAsset::register($this);
    }
    */
    backend\assets\AppTpvAsset::register($this);

    dmstr\web\AdminLteAsset::register($this);

    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');

    kartik\growl\GrowlAsset::register($this);
    kartik\base\AnimateAsset::register($this);

    ?>
    <?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <?php
            $this->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'href' => Url::to(['/favicon.png'], GlobalFunctions::URLTYPE)]);
        ?>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>

    </head>
    <body>

    <?php $this->beginBody() ?>
        <header data-role="header" id="header"> </header>
        <?= $this->render(
            'header-tpv.php',
            ['directoryAsset' => $directoryAsset]
        ) ?>
        
        <?= $this->render(
            'content-tpv.php',
            ['content' => $content, 'directoryAsset' => $directoryAsset]
        ) ?>
    
    <?php $this->endBody() ?>

    <?php
    $this->registerJsFile('@web/js/jquery.slimscroll.min.js');
    $this->registerJsFile('@web/plugins/chartjs-2.9.3/Chart.bundle.min.js');
    $this->registerJsFile('@web/plugins/chartjs-2.9.3/Chart.min.js');
    $this->registerJsFile('@web/js/demo.js');
    $this->registerJsFile('@web/js/customJS.js');

    ?>

    </body>
    </html>

    <?php $this->endPage() ?>


<?php } ?>
