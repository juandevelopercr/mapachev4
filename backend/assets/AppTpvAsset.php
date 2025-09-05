<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppTpvAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/tpv/cutom.css',
        'css/tpv/awesomplete.css'
        ];
    public $js = [
        // ChartJS is register on theme/layout/main.php
        'js/tpv/script.js',        
        'js/tpv/jquery.touchSwipe.js',
        'js/tpv/jquery.horizonScroll.js',
        'js/tpv/awesomplete.js',
        'js/tpv/jquery.print.min.js',

        'js/tpv/jquery.slideandswipe.js',
        'js/tpv/jRespond.js',
        'js/tpv/script_util5.js',
        'js/loadingoverlay/loadingoverlay.js',
        'js/tpv/js_dependencies_rsvp-3.1.0.min.js',
        'js/tpv/js_dependencies_sha-256.min.js',
        'js/tpv/js_qz-tray.js',
        'js/tpv/bridge.js',            
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}