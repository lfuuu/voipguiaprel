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
    'id' => 'voipgui',
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
                '' => 'site/index',
                'routing' => 'category/routing',
                'billing' => 'category/billing',
                'network' => 'category/network',
                'marketplace' => 'category/marketplace',
                'marketplace-eu' => 'category/marketplace-eu',
                'settings' => 'category/settings',
                'camel' => 'category/camel',
                'api-billing' => 'category/api-billing',
                'sms' => 'category/sms',
                'change_password' => 'category/change-password',
                'login' => 'site/login',
                'logout' => 'site/logout',
                'gen-passwd' => 'site/gen-passwd',
                's<serverId>' => 'server/index',
                's<serverId>/<action>' => 'server/<action>',
                'cs<serverId>' => 'camel/index',
                'cs<serverId>/<action>' => 'camel/<action>',
                'ms<serverId>' => 'sms/index',
                'ms<serverId>/<action>' => 'sms/<action>',
                'abs<serverId>' => 'api-billing/index',
                'abs<serverId>/<action>' => 'api-billing/<action>',
                'c<versionId>' => 'config/index',
                'c<versionId>/<action>' => 'config/<action>',
                'p<pricelistId>' => 'pricelist/index',
                'json/camel/<controller>/<action>' => 'json/camel/<controller>/<action>',
                'json/api_billing/<controller>/<action>' => 'json/api_billing/<controller>/<action>',
                'json/settings/<controller>/<action>' => 'json/settings/<controller>/<action>',
                'json/routing/<controller>/<action>' => 'json/routing/<controller>/<action>',
                'json/billing/<controller>/<action>' => 'json/billing/<controller>/<action>',
                'json/sms/<controller>/<action>' => 'json/sms/<controller>/<action>',
                'json/network/<controller:[\w\-]+>/<action:\w+>' => 'json/network/<controller>/<action>',
                'json/network/node-link/<action:[\w\-]+>' => 'json/network/node-link/<action>',
                'json/network/node/<action:[\w\-]+>' => 'json/network/node/<action>',
                'json/<controller>/<action>' => 'json/<controller>/<action>',
                'json/cdr/export-to-excel' => 'json/cdr/export-to-excel',
                '<controller>/<action>' => '<controller>/<action>',
                'json/settings/get-nas-ip-address' => 'json/settings/get-nas-ip-address',
                'json/corm-adapter/<action>' => 'json/corm-adapter/<action>',
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