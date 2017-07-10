<?php

define('CONFIG_FILEPATH', __DIR__ . '/config.local.php');
define('RESULT_FILEPATH', __DIR__ . '/../assets/healthData.json');

try {
    if (!file_exists(CONFIG_FILEPATH)) {
        throw new \Exception('Can\'t load configuration');
    }

    $config = require_once __DIR__ . '/config.php';
    if (!array_key_exists('resources', $config)) {
        throw new \Exception('List of monitoring resource is empty');
    }
} catch (\Exception $e) {
    $result = [
        'alert' => $e->getMessage(),
        'lastUpdate' => date('Y-m-d H:i:s'),
    ];

    file_put_contents(RESULT_FILEPATH, json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT));
    die;
}

$result = [
    'lastUpdate' => date('Y-m-d H:i:s'),
];

foreach ($config['resources'] as $resource) {
    $resourceData = parse_url($resource);

    $data = getData($resource);
    $dataJSON = json_decode($data, $assoc = true);

    if (json_last_error() === JSON_ERROR_NONE) {
        $result[$resourceData['host']] = array_merge($dataJSON, [
            'resourceUrl' => $resource,
        ]);
    } else {
        $result[$resourceData['host']] = $data;
    }
}

file_put_contents(RESULT_FILEPATH, json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT));

/**
 * @param string $resource
 * @return string
 */
function getData($resource)
{
    $options = [
        CURLOPT_URL => $resource,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_NOBODY => false,
        CURLOPT_HEADER => false,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ];

    $request = curl_init();
    curl_setopt_array($request, $options);

    $response = curl_exec($request);
    $content = curl_errno($request) ? curl_error($request) : $response;
    curl_close($request);

    return $content;
}
