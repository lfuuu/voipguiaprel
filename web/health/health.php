<?php

define('CONFIG_FILEPATH', __DIR__ . '/config.php');
define('RESULT_FILEPATH', __DIR__ . '/../assets/healthData.json');


$nowStr = (new \DateTime('now', new \DateTimeZone('Europe/Moscow')))->format('Y-m-d H:i:s');

try {
    if (!file_exists(CONFIG_FILEPATH)) {
        throw new \Exception('Can\'t load configuration');
    }

    $config = require_once CONFIG_FILEPATH;
    if (!array_key_exists('resources', $config)) {
        throw new \Exception('List of monitoring resource is empty');
    }
} catch (\Exception $e) {
    $result = [
        'alert' => $e->getMessage(),
        'lastUpdate' => $nowStr,
    ];

    file_put_contents(RESULT_FILEPATH, json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT));
    die;
}

$result = [
    'lastUpdate' => $nowStr,
];

foreach ($config['resources'] as $resource) {
    $title = '';

    if (is_array($resource)) {
        isset($resource['title']) && $title = $resource['title'];
        if (isset($resource['url'])) {
            $url = $resource['url'];
        } else {
            continue; // url not set
        }
    } else {
        $url = $resource;
    }

    $resourceData = parse_url($url);
    !$title && $title = $resourceData['host'];

    $data = getData($url);
    $dataJSON = json_decode($data, $assoc = true);

    if (json_last_error() === JSON_ERROR_NONE) {
        $result[$title] = array_merge($dataJSON, [
            'resourceUrl' => $url,
        ]);
    } else {
        $result[$title] = $data;
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
