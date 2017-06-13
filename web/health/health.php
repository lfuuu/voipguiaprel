<?php

define('CONFIG_FILEPATH', __DIR__ . '/config.php');
define('RESULT_FILEPATH', __DIR__ . '/../assets/healthData.json');

getServerList();

/**
 * @throws \Exception
 */
function getServerList()
{
    try {
        if (!file_exists(CONFIG_FILEPATH)) {
            throw new \Exception('Can\'t load configuration');
        }

        $config = require_once __DIR__ . '/config.php';
        if (!array_key_exists('resources', $config)) {
            throw new \Exception('List of monitoring resource is empty');
        }
    } catch (PDOException $e) {
        $serversData = [
            'alert' => $e->getMessage(),
            'lastUpdate' => date('Y-m-d H:i:s'),
        ];

        file_put_contents(RESULT_FILEPATH, json_encode($serversData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT));
        die;
    }

    $serversData = [
        'lastUpdate' => date('Y-m-d H:i:s'),
    ];

    foreach ($config['resources'] as $resource) {
        $data = getData($resource);
        $dataJSON = json_decode($data);

        if (json_last_error() === JSON_ERROR_NONE) {
            $serversData[$resource] = $dataJSON;
        } else {
            $serversData[$resource] = $data;
        }
    }

    file_put_contents(RESULT_FILEPATH, json_encode($serversData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT));
}

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
    ];

    $request = curl_init();
    curl_setopt_array($request, $options);

    $response = curl_exec($request);
    $content = curl_errno($request) ? curl_error($request) : $response;
    curl_close($request);

    return $content;
}