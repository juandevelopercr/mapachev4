<?php

use mdm\admin\components\Helper;
use backend\models\settings\Setting;
use common\models\User;
use backend\widgets\CustomMenu;
use common\models\GlobalFunctions;
use backend\models\settings\Issuer;
?>

<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">

        </div>

        <?php
        $menu_items = [
            [
                'label' => Yii::t("backend","Inicio"),
                'icon' => 'home',
                'url' => ['/site/index'],
            ],

            [
                'label' => Yii::t("backend","Clientes"),
                'icon' => 'address-card-o',
                'url' => ['/customer/index'],
            ],

            ['label' => Yii::t("backend", "Facturas electrónicas"), 'icon' => 'newspaper-o', 'url' => ['/invoice/index'],],
            ['label' => Yii::t("backend", "Notas de crédito"), 'icon' => 'book', 'url' => ['/credit-note/index'],],
            ['label' => Yii::t("backend", "Gastos"), 'icon' => 'clone', 'url' => ['/documents/index']],
            ['label' => Yii::t("backend","Servicios"), 'icon' => 'circle', 'url' => ['/service/index']],     
            ['label' => Yii::t("backend","Archivos"), 'icon' => 'file', 'url' => ['/file/index']],     
            
            [
                'label' => Yii::t("backend", "Parámetros de sistema"),
                'icon' => 'cogs',
                'url' => '#',
                'visible'=> (GlobalFunctions::getRol() === User::ROLE_ADMIN || GlobalFunctions::getRol() === User::ROLE_SUPERADMIN),
                'items' => [
                    [
                        'label' => Yii::t("backend","Usuarios"),
                        'icon' => 'circle',
                        'url' => ['/security/user'],
                    ],

                    [
                        'label' => Yii::t("backend", "Emisor"),
                        'icon' => 'circle',
                        'url' => ['/setting/update_issuer', 'id' => Issuer::getIdentificator()],
                        'visible' => GlobalFunctions::getRol() === User::ROLE_SUPERADMIN,
                    ],

                    /*
                    [
                        'label' => Yii::t("backend", "Correos de alerta"),
                        'icon' => 'circle',
                        'url' => ['/setting/update-email-alert', 'id' => Setting::getIdSettingByActiveLanguage()],
                    ],

                    [
                        'label' => Yii::t("backend", "Tablas"),
                        'icon' => 'circle',
                        'url' => '#',
                        'visible'=> (GlobalFunctions::getRol() === User::ROLE_ADMIN || GlobalFunctions::getRol() === User::ROLE_SUPERADMIN),
                        'items' => [
                            [
                                'label' => Yii::t("backend","Bancos"),
                                'icon' => 'circle',
                                'url' => ['/banks/index'],
                            ],                                
                            ['label' => Yii::t("backend", "Familias"), 'icon' => 'circle-o', 'url' => ['/family/index'],],
                            ['label' => Yii::t("backend", "Categorías"), 'icon' => 'circle-o', 'url' => ['/category/index'],],
                            ['label' => Yii::t("backend", "Condiciones de ventas"), 'icon' => 'circle-o', 'url' => ['/condition-sale/index'],],
                            ['label' => Yii::t("backend", "Cabys"), 'icon' => 'circle-o', 'url' => ['/cabys/index'],],
                            ['label' => Yii::t("backend", "Sucursales"), 'icon' => 'circle-o', 'url' => ['/branch-office/index'],],
                            ['label' => Yii::t("backend", "Cajas"), 'icon' => 'circle-o', 'url' => ['/boxes/index'], ],
                            ['label' => Yii::t("backend", "Departamentos"), 'icon' => 'circle-o', 'url' => ['/department/index'],],
                            ['label' => Yii::t("backend", "Agentes"), 'icon' => 'circle-o', 'url' => ['/agent/index'],],
                            ['label' => Yii::t("backend", "Rutas de transporte"), 'icon' => 'circle-o', 'url' => ['/route-transport/index'],],
                            ['label' => Yii::t("backend", "Proyectos"), 'icon' => 'circle-o', 'url' => ['/project/index'],],
                            ['label' => Yii::t("backend", "Tipos de clientes"), 'icon' => 'circle-o', 'url' => ['/customer-type/index'],],
                            ['label' => Yii::t("backend", "Tipos de inventarios"), 'icon' => 'circle-o', 'url' => ['/inventory-type/index'],],
                            ['label' => Yii::t("backend", "Unidades de medidas"), 'icon' => 'circle-o', 'url' => ['/unit-type/index'],],
                            ['label' => Yii::t("backend", "Medios de pagos"), 'icon' => 'circle-o', 'url' => ['/payment-method/index'],],
                            ['label' => Yii::t("backend", "Monedas"), 'icon' => 'circle-o', 'url' => ['/currency/index'],],
                            ['label' => Yii::t("backend", "Tipos de impuestos"), 'icon' => 'circle-o', 'url' => ['/tax-type/index'],],
                            ['label' => Yii::t("backend", "Tarifas de impuestos"), 'icon' => 'circle-o', 'url' => ['/tax-rate-type/index'],],
                            //['label' => Yii::t("backend", "Puntos de ventas"), 'icon' => 'circle-o', 'url' => ['/points-sale/index'], ],
                            ['label' => Yii::t("backend", "Denominaciones monedas"), 'icon' => 'circle-o', 'url' => ['/coin-denominations/index'],],                                                                            
                        ],
                    ],
                    */
                ],
            ],

            [
                'label' => Yii::t("backend", "Reportes"),
                'icon' => 'external-link-square',
                'url' => '#',
                'items' => [
                    ['label' => Yii::t("backend", "Facturas electrónicas"), 'icon' => 'circle', 'url' => ['/reportes/invoice/index'],],
                    ['label' => Yii::t("backend", "Recepción de Documentos"), 'icon' => 'circle', 'url' => ['/reportes/documents/index'],],                    
                ],
            ],

            //Desarrolladores
            [
                'label' => Yii::t("backend", "DESARROLLADORES"),
                'icon' => 'warning',
                'url' => '#',   
                'visible' =>  GlobalFunctions::getRol() === User::ROLE_SUPERADMIN,             
                'items' => [
                    [
                        'label' => Yii::t("backend", "Configuración de sistema"),
                        'icon' => 'cogs',
                        'url' => ['/setting/update', 'id' => Setting::getIdSettingByActiveLanguage()],
                    ],
                    [
                        'label' => Yii::t("backend", "Seguridad"),
                        'icon' => 'circle-o',
                        'url' => '#',
                        'items' => [
                            [
                                'label' => Yii::t("backend","Usuarios"),
                                'icon' => 'circle',
                                'url' => ['/security/user'],
                            ],
                            [
                                'label' => Yii::t("backend", "Rutas"),
                                'icon' => 'circle',
                                'url' => ['/security/route/index/'],
                            ],

                            [
                                'label' => Yii::t("backend", "Permisos"),
                                'icon' => 'circle',
                                'url' => ['/security/permission'],
                            ],
                            [
                                'label' => Yii::t("backend", "Roles"),
                                'icon' => 'circle',
                                'url' => ['/security/role'],
                            ],
                        ],
                    ],

                    [
                        'label' => Yii::t('backend', 'Envío de correo'),
                        'icon' => 'envelope',
                        'url' => ['/config-mailer/update', 'id' => 1],
                    ],

                    [
                        'label' => Yii::t("backend", "Países"),
                        'icon' => 'globe',
                        'url' => ['/country/index'],
                        'visible'=> false
                    ],

                    [
                        'label' => Yii::t("backend", "Idiomas"),
                        'icon' => 'flag',
                        'url' => ['/language/index'],
                        'visible'=> false
                    ],

                    [
                        'label' => Yii::t("backend", "Traducciones"),
                        'icon' => 'language',
                        'url' => '#',
                        'visible'=> false,
                        'items' => [
                            ['label' => Yii::t("backend", "Listado"), 'icon' => 'list', 'url' => ['/source-message/index'],],
                            ['label' => Yii::t("backend", "Importar"), 'icon' => 'upload', 'url' => ['/source-message/import'],],

                        ],
                    ],

                    [
                        'label' => Yii::t("backend", "Soporte"),
                        'icon' => 'cog',
                        'url' => '#',
                        'visible'=> false,
                        'items' => [
                            [
                                'label' => Yii::t('backend', 'Grupos de FAQ'),
                                'icon' => 'list',
                                'url' => ['/faq-group/index'],
                                'visible'=> false
                            ],

                            [
                                'label' => Yii::t('backend', 'FAQ'),
                                'icon' => 'question',
                                'url' => ['/faq/index'],
                                'visible'=> false
                            ],

                            [
                                'label' => Yii::t("backend", "Documentación API"),
                                'icon' => 'book',
                                'url' => ['/api-doc/index'],
                                'visible'=> false
                            ],
                            [
                                'label' => Yii::t("backend", "Tareas de CronJob"),
                                'icon' => 'clock-o',
                                'url' => ['/cronjob-task/index'],
                                'visible'=> false
                            ],
                            [
                                'label' => Yii::t("backend", "Trazas de CronJob"),
                                'icon' => 'line-chart',
                                'url' => ['/cronjob-log/index'],
                                'visible'=> false
                            ],
                            [
                                'label' => Yii::t("backend", "Trazas API"),
                                'icon' => 'line-chart',
                                'url' => ['/api-request-log/index'],
                                'visible'=> false
                            ],
                        ],
                    ],

                    //['label' => 'Phpinfo', 'icon' => 'info-circle', 'url' => ['/site/phpinfo'], 'target'=>'_blank', 'visible' => Yii::$app->user->can(User::ROLE_SUPERADMIN)],

                    //['label' => 'Gii', 'icon' => 'file-code-o', 'url' => ['/gii/default'], 'target'=>'_blank', 'visible' => Yii::$app->user->can(User::ROLE_SUPERADMIN) && YII_ENV_DEV],

                    //['label' => 'Debug', 'icon' => 'dashboard', 'url' => ['/debug'], 'target'=>'_blank', 'visible' => Yii::$app->user->can(User::ROLE_SUPERADMIN) && YII_ENV_DEV],
                ],
                'visible' => (GlobalFunctions::getRol() == User::ROLE_SUPERADMIN && YII_ENV_DEV)
            ],
        ];


        $menu_items = Helper::filter($menu_items);

        ?>

        <?= CustomMenu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => $menu_items
            ]
        ) ?>

    </section>

</aside>
