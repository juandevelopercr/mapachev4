<?php

return [

    'components' => [

        'db' => [
   'class' => 'yii\db\Connection',

            'dsn' => 'pgsql:host=localhost;port=5432;dbname=herbavic_herbavi', // PostgreSQL

            'username' => 'herbavic_admincr',

            'password' => '3P610LFQ',

            'charset' => 'utf8',

        ],

		'mailer' => [

			'class' => 'yii\swiftmailer\Mailer',

			'viewPath' => '@common/mail',

			'useFileTransport' => false,//set this property to false to send mails to real email addresses

										//comment the following array to send mail using php's mail function

   

            'transport' => [

            	'class'         => 'Swift_SmtpTransport',

            	'host'          => 'server.herbavicr.com',

            	'username'      => 'portal@herbavicr.com',

            	'password'      => 'WyO7y4bWn1Pm',

            	'port'          => '587',

            	'encryption'    => 'tls'

            ],

           

		], 



        /*

        'mailer' => [

            'class' => 'yii\swiftmailer\Mailer',

            'viewPath' => '@common/mail',

        ],

        */

    ],

];

