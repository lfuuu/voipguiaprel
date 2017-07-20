<?php

$config = [
    'resources' => [],
];

if (file_exists($file = __DIR__ . '/config.local.php')) {
    $config = array_merge($config, require_once $file);
}

return $config;