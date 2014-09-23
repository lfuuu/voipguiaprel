<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

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

return [
    'id' => 'voipgui-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'db' => $db,
        'log' => $log,
    ],
    'params' => $params,
];
