<?php

use common\models\GlobalFunctions;
use kartik\mpdf\Pdf;

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'advanced-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'language' => 'es',
    'sourceLanguage' => 'es',
    'modules' => [
        'security' => [
            'class' => 'mdm\admin\Module',
            'controllerMap' => [
                'user' => [
                    'class' => 'backend\controllers\UserController',
                ],
                'route' => [
                    'class' => 'backend\controllers\RouteController',
                ],
                'role' => [
                    'class' => 'backend\controllers\RoleController',
                ],
                'permission' => [
                    'class' => 'backend\controllers\PermissionController',
                ],
                //*** disable routes  for default, menu, permission and rule sections of yii2-admin
                'default' => [
                    'class' => 'backend\controllers\DisableRoutesRbacController',
                ],
                'menu' => [
                    'class' => 'backend\controllers\DisableRoutesRbacController',
                ],
                'rule' => [
                    'class' => 'backend\controllers\DisableRoutesRbacController',
                ],
                'assignment' => [
                    'class' => 'backend\controllers\DisableRoutesRbacController',
                ],

            ],

        ],
	    'gridview' =>  [
		    'class' => '\kartik\grid\Module'
		    // enter optional module parameters below - only if you need to
		    // use your own export download action or custom translation
		    // message source
		    // 'downloadAction' => 'gridview/export/download',
		    // 'i18n' => []
	    ],
        'v1' => [
            'basePath' => '@backend/modules/v1',
            'class' => 'backend\modules\v1\ApiModule',
        ],
        'reportes' => [
            'class' => 'backend\modules\reportes\Module',
        ],    
        'tpv' => [
            'class' => 'backend\modules\tpv\Module',
        ],              
        'dynagrid'=>[
            'class'=>'\kartik\dynagrid\Module',
            // other settings (refer documentation)
        ],
    ],
    'as access' => [
	    'class' => 'mdm\admin\components\AccessControl',
	    'allowActions' => [
		    'site/*',
		    'v1/*',
		    //'security/*',
            'notifications/*',
            'security/user/request-password-reset',
            'security/user/reset-password',
		    'gii/*',
            'debug/*',
            'api/*'
	    ]
    ],
    'components' => [
	    'view' => [
		    'theme' => [
			    'pathMap' => [
				    '@vendor/mdmsoft/yii2-admin/views/user' => '@app/views/custom_views_yii2_admin/user',
				    '@vendor/mdmsoft/yii2-admin/views/assignment' => '@app/views/custom_views_yii2_admin/assignment',
				    '@vendor/mdmsoft/yii2-admin/views/item' => '@app/views/custom_views_yii2_admin/item',
				    '@vendor/mdmsoft/yii2-admin/views/route' => '@app/views/custom_views_yii2_admin/route',
			    ],
		    ],
	    ],
        'request' => [
            'csrfParam' => '_csrf-backend',
            'baseUrl' => '',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'user' => [
	        'identityClass' => 'common\models\User',
	        'loginUrl' => ['security/user/login'],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-session',
        ],
        /*
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    //'levels' => ['error', 'warning'],
                    'logVars' => ['_GET', '_POST'],
                    'categories' => ['WebFactory'],  // Use you App Short Name
                    'logFile' => '@runtime/logs/web_factory.log'
                ],
            ],
        ],
        */
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'], // Aquí puedes agregar otros niveles como 'error', 'warning', etc.
                    'categories' => ['application'], // Solo mensajes de la categoría 'application'
                    'logFile' => '@runtime/logs/app.log',
                    'logVars' => [], // Eliminar variables globales como $_SERVER, $_POST, etc.
                    //'maxFileSize' => 1024 * 2, // Tamaño máximo del archivo de log en KB
                    //'maxLogFiles' => 5, // Número máximo de archivos de log
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        'urlManager' => [
            'baseUrl' => GlobalFunctions::BASE_URL,  //Real domain
            'showScriptName' => false,
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'rules' => [
                ['class'=> 'yii\rest\UrlRule','controller' => 'v1/auth','pluralize' => false],
                ['class'=> 'yii\rest\UrlRule','controller' => 'v1/user','pluralize' => false],
                ['class'=> 'yii\rest\UrlRule','controller' => 'v1/customer','pluralize' => false, 'only' => ['index']],
                ['class'=> 'yii\rest\UrlRule','controller' => 'v1/purchase-order','pluralize' => false,'extraPatterns' => [
                    'POST update/<id:\d+>' => 'update',
                ]],
                ['class'=> 'yii\rest\UrlRule','controller' => 'v1/item-purchase-order','pluralize' => false,'extraPatterns' => [
                    'POST update/<id:\d+>' => 'update',
                ]],
                ['class'=> 'yii\rest\UrlRule','controller' => 'v1/nomenclators','pluralize' => false,'extraPatterns' => [
                    'GET search_product_service/<name:\d+>' => 'search_product_service',
                ]],
                ['class'=> 'yii\rest\UrlRule','controller' => 'v1/hacienda','pluralize' => false],
                ['class'=> 'yii\rest\UrlRule','controller' => 'v1/smtp','pluralize' => false],
                ['class'=> 'yii\rest\UrlRule','controller' => 'v1/send-invoice','pluralize' => false],
                ['class'=> 'yii\rest\UrlRule','controller' => 'v1/send-document','pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => 'api','pluralize' => false ],
            ]
        ],
//        'mail' => [
//            'class' => 'yii\swiftmailer\Mailer',
//            'viewPath' => '@common/mail',
//        ],
        'pdf' => [
            'class' => Pdf::classname(),
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_BROWSER,
            // refer settings section for all configuration options
        ],
        'mail' => [
            'class' => 'backend\mail\CustomMailer',
            'viewPath' => '@common/mail',
            'enableSwiftMailerLogging' => true,
            'useFileTransport' => false,
        ],
        'formatter' => [
            //'class' => 'yii\i18n\formatter',
            'thousandSeparator' => '.',
            'decimalSeparator' => ',',
            'currencyCode' => '¢'
        ],
    ],
    'as beforeRequest' => [
	    'class' => 'backend\components\CheckIfLoggedIn'
    ],
    'params' => $params,
];
