<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'plugins/chartjs-2.9.3/Chart.min.css'
        ];
    public $js = [
        // ChartJS is register on theme/layout/main.php
        'js/bootbox.min.js',
        'js/loadingoverlay/loadingoverlay.js',
		'fusioncharxt/js/fusioncharts.js',
		'fusioncharxt/js/themes/fusioncharts.theme.fint.js?cacheBust=56',
		'fusioncharxt/js/themes/fusioncharts.theme.carbon.js?cacheBust=56',
		'fusioncharxt/js/themes/fusioncharts.theme.ocean.js?cacheBust=56',
		'fusioncharxt/js/themes/fusioncharts.theme.zune.js?cacheBust=56',	        
    ];
    public $depends = [
        'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
    ];
}
