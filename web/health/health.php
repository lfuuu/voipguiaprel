<?php

define('MONITOR_SCHEME', 'http');
define('MONITOR_PORT', 8032);
define('MONITOR_URI', '/test/healthcheck');

define('PGSQL_DNS', 'pgsql:host=85.94.32.235;port=5432;dbname=nispd');
define('PGSQL_USER', 'readonly');
define('PGSQL_PASSWORD', 'readonly');
define('PGSQL_CHARSET', 'utf8');

define('RESULT_FILEPATH', __DIR__ . '/../assets/healthData.json');

getServerList();

/**
 * @inheritdoc
 */
function getServerList()
{
    try {
        $pdo = new PDO(PGSQL_DNS, PGSQL_USER, PGSQL_PASSWORD);
        $pdo->exec("SET SESSION TIME ZONE 'UTC';");
    } catch (PDOException $e) {
        echo 'Can\'t establish connection (' . $e->getMessage() . ')' . PHP_EOL;
        die;
    }

    $query = $pdo->query('SELECT hostname FROM public.server WHERE is_need_db_do_migrate ORDER BY id ASC');
    $serversData = [
        'lastUpdate' => date('Y-m-d H:i:s'),
    ];
    while ($hostname = $query->fetch(PDO::FETCH_COLUMN)) {
        $hostData = getHostData($hostname);
        $hostDataJSON = json_decode($hostData);

        if (json_last_error() === JSON_ERROR_NONE) {
            $serversData[$hostname] = $hostDataJSON;
        } else {
            $serversData[$hostname] = $hostData;
        }
    }

    file_put_contents(RESULT_FILEPATH, json_encode($serversData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT));

    $pdo = null;
}

/**
 * @param string $hostname
 * @return string
 */
function getHostData($hostname)
{
    $options = [
        CURLOPT_URL => MONITOR_SCHEME . '://' . $hostname . ':' . MONITOR_PORT . MONITOR_URI,
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