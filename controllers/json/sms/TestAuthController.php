<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\classes\traits\TestResult;
use app\models\auth\SmsTrunk;
use app\models\ServerOcs;
use app\models\auth\SmsGate;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class TestAuthController extends JsonController
{
    use TestResult;

    const TEST_RESULT_DEFAULT_DEPTH  = 1;
    const TEST_RESULT_INITIAL_DEPTH  = 2;

    const TEST_RESULT_DIVIDER_START  = '2B2EKSTARTJSON';
    const TEST_RESULT_DIVIDER_STOP   = '2B2EKSTOPJSON';

    protected $modelName        = 'app\models\auth\SmsTestAuth';
    protected $idParamName      = 'id';
    protected $nameParamName    = 'name';
    protected $readWhere        = ['server_id'];
    protected $createPermission = 'sms_test_auth_create';
    protected $listPermission   = 'sms_test_auth_list';
    protected $editPermission   = 'sms_test_auth_edit';
    protected $deletePermission = 'sms_test_auth_delete';

    private $stepParamName      = 'nodes';
    private $_oldTestResultTypes = ['ERROR', 'RESULT', 'INFO', 'HEADER'];

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws HttpException
     */
    public function actionRead()
    {
        if (!\Yii::$app->user->can('sms_test_auth_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName   = $this->modelName;
        $searchArray = $this->request['search_array'];
        $testGroupId = $searchArray['group_id'] ?? '';
        $gateId      = $searchArray['gate_id']  ?? '';

        $groupWhere = $testGroupId === '' ? 'true' : ['auth.a2p_test_auth.a2p_testgroup_id' => $testGroupId];
        $gateWhere  = $gateId      === '' ? []     : ['auth.a2p_test_auth.gate_id'           => $gateId];

        $query = $modelName::find()
            ->select([
                'auth.a2p_test_auth.*',
                'testgroup_name' => 'tg.group_name',
                'trunk_name_out' => 'r.name',
                'passed'         => 'trs.passed',
                'gate_id',
                'gate_name'      => 'g.name',
            ])
            ->leftJoin('auth.a2p_testgroup tg', 'tg.id = auth.a2p_test_auth.a2p_testgroup_id')
            ->leftJoin('auth.a2psms_route r',   'cast(r.id as varchar(10)) = auth.a2p_test_auth.trunk_name')
            ->leftJoin('auth.a2p_test_result trs', 'trs.id_auth = auth.a2p_test_auth.id')
            ->leftJoin('auth.sms_gate g',        'g.id = auth.a2p_test_auth.gate_id')
            ->where($groupWhere)
            ->andWhere($gateWhere)
            ->orderBy('auth.a2p_test_auth.name')
            ->asArray();

        if (!empty($searchArray['name'])) {
            $query->andWhere('auth.a2p_test_auth.name ilike :name')
                  ->addParams([':name' => '%' . $searchArray['name'] . '%']);
        }
        if (!empty($searchArray['trunk'])) {
            $query->andWhere(['like', 'r.name', $searchArray['trunk']]);
        }
        if (!empty($searchArray['id'])) {
            $query->andWhere('auth.a2p_test_auth.id = :id')
                  ->addParams([':id' => $searchArray['id']]);
        }
        if (($searchArray['result'] ?? '') !== '') {
            if ($searchArray['result'] === 'success') {
                $query->andWhere(['trs.passed' => true]);
            } elseif ($searchArray['result'] === 'failure') {
                $query->andWhere(['trs.passed' => false]);
            }
        }

        return $query->all();
    }

    public function actionResult()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;
        $item = $modelName::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, $this->modelName . ' не найден');
        }

        $server = ServerOcs::findOne($item->server_id);
        if ($server === null) {
            throw new HttpException(404, 'Сервер для ' . $this->modelName . ' не найден');
        }

        $gate = $item->gate_id ? SmsGate::findOne($item->gate_id) : null;
        $gateType = strtoupper(trim((string)($gate->type ?? ''))); // 'MCMCN' или 'YATE' и т.п.

        $isReserve = (!empty($this->request['is_reserve']) && $this->request['is_reserve'] === true);
        $baseUrl   = $isReserve ? $server->camel_reserve : $server->camel_gw;

        // Достаём host и принудительно уходим на порт 8103
        $parsed = parse_url($baseUrl);
        $host   = $parsed['host'] ?? ($parsed['path'] ?? $baseUrl);
        $host   = preg_replace('~^https?://~i', '', (string)$host);
        $host   = preg_replace('~/.*$~', '', $host);
        $base   = 'http://' . $host . ':8103';

        $trunk = SmsTrunk::findOne(['id' => $item->trunk_name]);
        if ($trunk === null) {
            throw new HttpException(404, 'Транк не найден');
        }

        // --- HTTP helpers (GET / POST) ---
        $sendGet = function (string $fullUrl, array $headers = []) {
            $ch = curl_init($fullUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER    => true,
                CURLOPT_HTTPGET           => true,
                CURLOPT_HTTPHEADER        => $headers,
                CURLOPT_HEADER            => true,
                CURLOPT_TIMEOUT_MS        => 15000,
                CURLOPT_CONNECTTIMEOUT_MS => 1000,
            ]);
            $raw    = curl_exec($ch);
            $status = 0;
            $hdrSz  = 0;
            if ($raw !== false) {
                $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
                $hdrSz  = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            }
            curl_close($ch);
            if ($raw === false) {
                return [null, 0, []];
            }
            $hdrs = preg_split("/\r\n|\n|\r/", trim(substr($raw, 0, $hdrSz)));
            $body = substr($raw, $hdrSz);
            return [$body, $status, $hdrs];
        };

        $sendPost = function (string $endpoint, string $body, array $headers) {
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER    => true,
                CURLOPT_POST              => true,
                CURLOPT_POSTFIELDS        => $body,
                CURLOPT_HTTPHEADER        => $headers,
                CURLOPT_HEADER            => true,
                CURLOPT_TIMEOUT_MS        => 15000,
                CURLOPT_CONNECTTIMEOUT_MS => 1000,
            ]);
            $raw    = curl_exec($ch);
            $status = 0;
            $hdrSz  = 0;
            if ($raw !== false) {
                $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
                $hdrSz  = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            }
            curl_close($ch);
            if ($raw === false) {
                return [null, 0, []];
            }
            $hdrs = preg_split("/\r\n|\n|\r/", trim(substr($raw, 0, $hdrSz)));
            $resp = substr($raw, $hdrSz);
            return [$resp, $status, $hdrs];
        };

        // --- Ветки по типу шлюза ---
        $debug = [];
        if ($gateType === 'MCMCN') {
            // MCN → GET /api/get.dst_route?a_num=&b_num=&src_route=
            $endpoint = $base . '/api/get.dst_route';
            $query = [
                'a_num'    => $item->src_number,
                'b_num'    => $item->dst_number,
                'src_route'=> $trunk->name,
            ];
            $fullUrl = $endpoint . '?' . http_build_query($query);

            list($bodyUsed, $codeUsed, $headersUsed) = $sendGet($fullUrl, [
                'Accept: application/json',
            ]);

            $debug = [
                'endpoint'     => $endpoint,
                'http_code'    => $codeUsed,
                'headers'      => $headersUsed,
                'payload_mode' => 'query',
                'query'        => $query,
                'full_url'     => $fullUrl,
                'body_sample'  => mb_strimwidth((string)$bodyUsed, 0, 600, '…'),
            ];
        } else {
            // Yate/другие → POST JSON в /api/get.dst_route_smsc
            $endpoint = $base . '/api/get.dst_route_smsc';
            $payloadArr = [
                'trunk'  => $trunk->name,
                'caller' => $item->src_number,
                'called' => $item->dst_number,
                'trace'  => "true",
            ];
            $payloadJson = json_encode($payloadArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            list($bodyUsed, $codeUsed, $headersUsed) = $sendPost($endpoint, $payloadJson, [
                'Content-Type: application/json',
                'Accept: application/json',
            ]);

            $debug = [
                'endpoint'     => $endpoint,
                'http_code'    => $codeUsed,
                'headers'      => $headersUsed,
                'payload_mode' => 'json-object',
                'payload'      => $payloadArr,
                'body_sample'  => mb_strimwidth((string)$bodyUsed, 0, 600, '…'),
            ];
        }

        if ($bodyUsed === null) {
            throw new HttpException(502, 'Ошибка сети при обращении к внешнему API');
        }
        // Не валим на 406: CAMEL иногда так отвечает, но тело годное.
        if ($codeUsed >= 407) {
            throw new HttpException(502, 'Внешний API вернул ошибку: HTTP ' . $codeUsed . ' — ' . mb_strimwidth($bodyUsed, 0, 800, '…'));
        }

        // Ключ кэша
        $apiParams = [
            'user' => \Yii::$app->user->getId(),
            'date' => date('Y-m-d H:i:s'),
        ];
        $requestForKey = rtrim($debug['endpoint'] ?? $base, '/') . '?' . http_build_query($apiParams);
        $key = md5($requestForKey);

        // Парсим и сохраняем результат
        $result = $this->generateNewResult($bodyUsed, $key);

        return [
            'item'   => $item->toArray(),
            'name'   => 'root',
            'key'    => $key,
            'result' => $result,
            'debug'  => $debug,
        ];
    }

    /**
     * Устойчивый парсер JSON-ответа CAMEL:
     *  - поддерживает trace-как-строку (в т.ч. двойное экранирование);
     *  - добавляет верхний RESULT из dst_route/result, даже если нет дерева trace;
     *  - есть фолбэк извлечения dst_route/result регекспом из сырого тела.
     */
    private function generateNewResult($resultString, $key)
    {
        $raw = trim(str_replace(["\r", "\t"], "", (string)$resultString));

        // Попытка №1: обычный JSON
        $temp = json_decode(str_replace("\n", "", $raw), true);

        // Фолбэк: если вообще не JSON
        if (!is_array($temp)) {
            $nodes = [];
            $dst = null; $res = null;
            if (preg_match('~"dst_route"\s*:\s*"([^"]*)"~u', $raw, $m)) $dst = $m[1];
            if (preg_match('~"result"\s*:\s*"([^"]*)"~u',    $raw, $m)) $res = strtoupper($m[1]);
            if ($dst !== null || $res !== null) {
                $nodes[] = [
                    'type'    => 'RESULT',
                    'message' => sprintf('RESULT|%s|: %s', $res ?: '', $dst ?: ''),
                    'color'   => 'green',
                    'path'    => 0,
                ];
            }
            $result = $this->processResult($nodes, true);
            \Yii::$app->cache->set($key, $result);
            return $this->findByPath($result, '', 4);
        }

        // trace может быть объектом/массивом/строкой JSON
        $trace = $temp['trace'] ?? [];
        $decodeOnce = function ($s) {
            $d = json_decode($s, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $d : null;
        };
        if (is_string($trace)) {
            $t1 = $decodeOnce($trace);
            if (is_array($t1)) {
                $trace = $t1;
            } else {
                $t2 = $decodeOnce(stripslashes($trace));
                if (is_array($t2)) {
                    $trace = $t2;
                }
            }
        }

        $extractNodes = function ($t) {
            if (is_array($t)) {
                if (isset($t['nodes']) && is_array($t['nodes'])) return $t['nodes'];
                if (array_key_exists(0, $t) && is_array($t[0]))  return $t;
            }
            return [];
        };

        $nodes = [];

        // Добавим «шапку»-RESULT, если в корне есть dst_route/result
        if (array_key_exists('dst_route', $temp) || array_key_exists('result', $temp)) {
            $dst = isset($temp['dst_route']) ? (string)$temp['dst_route'] : '';
            $res = isset($temp['result'])    ? strtoupper((string)$temp['result']) : '';
            if ($dst !== '' || $res !== '') {
                $nodes[] = [
                    'type'    => 'RESULT',
                    'message' => sprintf('RESULT|%s|: %s', $res, $dst),
                    'color'   => 'green',
                    'path'    => 0,
                ];
            }
        }

        $nodes = array_merge($nodes, $extractNodes($trace));

        // Фолбэк, если узлов нет — достаём из сырого тела
        if (!$nodes) {
            $dst = null; $res = null;
            if (preg_match('~"dst_route"\s*:\s*"([^"]*)"~u', $raw, $m)) $dst = $m[1];
            if (preg_match('~"result"\s*:\s*"([^"]*)"~u',    $raw, $m)) $res = strtoupper($m[1]);
            if ($dst !== null || $res !== null) {
                $nodes[] = [
                    'type'    => 'RESULT',
                    'message' => sprintf('RESULT|%s|: %s', $res ?: '', $dst ?: ''),
                    'color'   => 'green',
                    'path'    => 0,
                ];
            }
        }

        $result = $this->processResult($nodes, true);
        \Yii::$app->cache->set($key, $result);
        return $this->findByPath($result, '', 4);
    }
}
