<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '********',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\Users',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
		
		        // other default components here..
        'jwt' => [
            'class' => \sizeg\jwt\Jwt::class,
            'key' => 'foodgenee_key',
			 'jwtValidationData' => \app\components\JwtValidationData::class,

        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                /*[
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'trace','error', 'warning'],
					'logVars' => [],
					'enabled' => YII_DEBUG,
                ],*/
				[
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\EmailTarget',
                    'levels' => ['error'],
                    'categories' => ['yii\db\*'],
                    'message' => [
                       'from' => ['log@example.com'],
                       'to' => ['admin@example.com', 'developer@example.com'],
                       'subject' => 'Database errors at example.com',
                    ],
                ],
				[
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error'],
					'logFile' => '@runtime/logs/error.log',
                ],
				[
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['warning'],
					'logFile' => '@runtime/logs/warning.log',
                ],
				[
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info','trace'],
					'logFile' => '@runtime/logs/info.log',
                ],
				
            ],
        ],
        'db' => $db,
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ]
		,
  'assetManager' => [
    'bundles' => [
        'yii\web\JqueryAsset' => [
            'js'=>[]
        ],
        'yii\bootstrap\BootstrapPluginAsset' => [
            'js'=>[]
        ],
        'yii\bootstrap\BootstrapAsset' => [
            'css' => [],
        ],

    ],
],
        
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
