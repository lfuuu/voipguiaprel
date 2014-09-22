<?php

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'language' => 'ru-RU',
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'Jkjh9834jkjhsHJK89834hjk338',
            'parsers' => [ 'application/json' => 'yii\web\JsonParser' ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'class' => 'app\classes\ErrorHandler',
            'errorAction' => 'site/error',
        ],
        'mail' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => 3,
            'flushInterval' => 1,
            'targets' => [
                [
                    'class' => 'app\classes\GraylogTarget',
                    'levels' => ['error', 'warning'],
                    'host' => '10.252.0.204',
                    'falicity' => 'web_voipauth_error',
                ],
                [
                    'class' => 'app\classes\GraylogTarget',
                    'levels' => ['info', 'trace'],
                    'categories' => ['application'],
                    'host' => '10.252.0.204',
                    'falicity' => 'web_voipauth_debug',
                ],
            ],
        ],
        'view' => [
            'class' => 'app\components\View',
        ],
/*        'cache' => [
            'class' => 'yii\caching\MemCache',
            'servers' => [
                [
                    'host' => '89.235.136.22',
                    'port' => 11211,
                    'persistent' => true,
                ],
            ],
        ],*/
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                '' => 'site/index',
                'login' => 'site/login',
                'logout' => 'site/logout',
                'gen-passwd' => 'site/gen-passwd',
                's<serverId>' => 'server/index',
                's<serverId>/<action>' => 'server/<action>',
                'c<versionId>' => 'config/index',
                'c<versionId>/<action>' => 'config/<action>',
                'json/<controller>/<action>' => 'json/<controller>/<action>',
                '<controller>/<action>' => '<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
}

return $config;
