<?php

$db = array_merge(
    require(__DIR__ . '/db.php'),
    require(__DIR__ . '/db-local.php')
);

$log = array_merge(
    require(__DIR__ . '/log.php'),
    require(__DIR__ . '/log-local.php')
);

$params = array_merge(
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

$config = [
    'id' => 'voipgui_exporter',
    'basePath' => dirname(__DIR__),
    'language' => 'ru-RU',
    'bootstrap' => ['log'],
    'components' => [
        'session' => [
            'class' => 'yii\web\DbSession',
            'cookieParams' => ['lifetime' => 6 * 60 * 60]
        ],
        'request' => [
            'cookieValidationKey' => 'Jkjh9834jkjhsHJK89834hjk338',
            'parsers' => [ 'application/json' => 'yii\web\JsonParser' ],
        ],
        'cache' => [
            'class' => 'yii\caching\DbCache'
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
        ],
        'errorHandler' => [
            'class' => 'app\classes\ErrorHandler',
            'errorAction' => 'site/error',
        ],
        'mail' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
        ],
        'log' => $log,
        'view' => 'app\components\View',
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
                '' => 'exporter/export',
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'defaultRoles' => [],
        ],
    ],
    'params' => $params,
];

Yii::setAlias('@app', dirname(__DIR__));

return $config;
