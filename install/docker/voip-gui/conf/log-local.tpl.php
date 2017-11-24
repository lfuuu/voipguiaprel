<?php

$debugLogging = false;
$graylogHost = 'graylog.mcn.ru';
$source = 'voipgui';

if ($debugLogging) {
    return [
        'targets' => [
            [
                'class' => 'app\classes\GraylogTarget',
                'levels' => ['error', 'warning', 'info', 'trace'],
                'host' => $graylogHost,
                'source' => $source,
            ],
        ],
    ];
} else {
    return [
        'targets' => [
            [
                'class' => 'app\classes\GraylogTarget',
                'levels' => ['error', 'warning'],
                'host' => $graylogHost,
                'source' => $source,
            ],
            [
                'class' => 'app\classes\GraylogTarget',
                'levels' => ['info', 'trace'],
                'categories' => ['application'],
                'host' => $graylogHost,
                'source' => $source,
            ],
        ],
    ];
}