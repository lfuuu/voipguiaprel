<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\classes\traits\TestResult;
use app\models\auth\SmsTrunk;
use app\models\ServerOcs;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class TestAuthController extends JsonController
{
    use TestResult;
    const TEST_RESULT_DEFAULT_DEPTH = 1;
    const TEST_RESULT_INITIAL_DEPTH = 2;

    const TEST_RESULT_DIVIDER_START = '2B2EKSTARTJSON';
    const TEST_RESULT_DIVIDER_STOP = '2B2EKSTOPJSON';
    protected $modelName = 'app\models\auth\SmsTestAuth';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $readWhere = ['server_id'];
    protected $createPermission = 'sms_test_auth_create';
    protected $listPermission = 'sms_test_auth_list';
    protected $editPermission = 'sms_test_auth_edit';
    protected $deletePermission = 'sms_test_auth_delete';
    private $stepParamName = 'nodes';

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

        $modelName    = $this->modelName;
        $searchArray  = $this->request['search_array'];
        $testGroupId  = $searchArray['group_id'] ?? '';
        $gateId       = $searchArray['gate_id']      ?? '';

        $groupWhere = $testGroupId === '' 
            ? 'true' 
            : ['auth.a2p_test_auth.a2p_testgroup_id' => $testGroupId];
        
        $gateWhere = $gateId === ''
            ? [] 
            : ['auth.a2p_test_auth.gate_id' => $gateId];

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
            ->leftJoin('auth.sms_gate g',        'g.id = auth.a2p_test_auth.gate_id')  // <-- связь на шлюзы
            ->where($groupWhere)
            ->andWhere($gateWhere)
            ->orderBy('auth.a2p_test_auth.name')
            ->asArray();

        if (!empty($searchArray['name'])) {
            $query->andWhere('auth.a2p_test_auth.name ilike :name');
            $query->addParams([':name' => '%' . $searchArray['name'] . '%']);
        }
        if (!empty($searchArray['trunk'])) {
    $query->andWhere(['like', 'r.name', $searchArray['trunk']]);
}
        if (!empty($searchArray['id'])) {
            $query->andWhere('auth.a2p_test_auth.id = :id');
            $query->addParams([':id' => $searchArray['id']]);
        }
        if ($searchArray['result'] !== '') {
            if ($searchArray['result'] == 'success') {
                $query->andWhere(['trs.passed' => true]);
            } elseif ($searchArray['result'] == 'failure') {
                $query->andWhere(['trs.passed' => false]);
            }
        }

        return $query->all();
    }


    public function actionResult()
{
    if (!\Yii::$app->user->can($this->listPermission)) {
        throw new \yii\web\ForbiddenHttpException('Access denied');
    }

    $modelName = $this->modelName;
    $item = $modelName::findOne($this->request['id']);
    if ($item === null) {
        throw new \yii\web\HttpException(404, $this->modelName . ' не найден');
    }

    $server = \app\models\ServerOcs::findOne($item->server_id);
    if ($server === null) {
        throw new \yii\web\HttpException(404, 'Сервер для ' . $this->modelName . ' не найден');
    }

    // ВАЖНО: подгружаем шлюз, чтобы узнать его type
    /** @var \app\models\auth\SmsGate $gate */
    $gate = \app\models\auth\SmsGate::findOne($item->gate_id);
    $gateType = $gate->type ?? null; // 'MCMCN' или 'Yate' и т.п.

    // Определяем базовый host так же, как раньше
    $isEuropean = \Yii::$app->params['isEuropean'] ?? false;

    $resolveHost = function() use ($server, $isEuropean) {
        if ($isEuropean) {
            // В EU мы раньше ходили на фиксированный IP; оставим тот же хост, порт одинаковый (8103)
            return '10.250.30.48';
        }
        $baseUrl = (!empty($this->request['is_reserve']) && $this->request['is_reserve'] === true)
            ? $server->camel_reserve
            : $server->camel_gw;
        $parsed = parse_url($baseUrl);
        $host   = $parsed['host'] ?? ($parsed['path'] ?? $baseUrl);
        $host   = preg_replace('~^https?://~i', '', (string)$host);
        $host   = preg_replace('~/.*$~', '', $host);
        return $host;
    };

    $host = $resolveHost();

    // Транк нам нужен и для старого, и для нового API
    $trunk = \app\models\auth\SmsTrunk::findOne(['id' => $item->trunk_name]);
    if ($trunk === null) {
        throw new \yii\web\HttpException(404, 'Транк не найден');
    }

    // Общие curl-хелперы
    $sendPostJson = function (string $url, string $body, array $headers) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_POST              => true,
            CURLOPT_POSTFIELDS        => $body,
            CURLOPT_HTTPHEADER        => $headers,
            CURLOPT_HEADER            => true,
            CURLOPT_TIMEOUT_MS        => 15000,
            CURLOPT_CONNECTTIMEOUT_MS => 1000,
        ]);
        $raw = curl_exec($ch);
        $status = 0; $hdrSize = 0;
        if ($raw !== false) {
            $status  = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $hdrSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        }
        curl_close($ch);
        if ($raw === false) return [null, 0, [], 'curl failed'];
        $headersRaw = substr($raw, 0, $hdrSize);
        $bodyRaw    = substr($raw, $hdrSize);
        $headersArr = preg_split("/\r\n|\n|\r/", trim($headersRaw));
        return [$bodyRaw, $status, $headersArr, null];
    };

    $sendGet = function (string $url, array $query, array $headers) {
        $full = $url . '?' . http_build_query($query);
        $ch = curl_init($full);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_HTTPGET           => true,
            CURLOPT_HTTPHEADER        => $headers,
            CURLOPT_HEADER            => true,
            CURLOPT_TIMEOUT_MS        => 15000,
            CURLOPT_CONNECTTIMEOUT_MS => 1000,
        ]);
        $raw = curl_exec($ch);
        $status = 0; $hdrSize = 0;
        if ($raw !== false) {
            $status  = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $hdrSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        }
        curl_close($ch);
        if ($raw === false) return [null, 0, [], 'curl failed'];
        $headersRaw = substr($raw, 0, $hdrSize);
        $bodyRaw    = substr($raw, $hdrSize);
        $headersArr = preg_split("/\r\n|\n|\r/", trim($headersRaw));
        return [$bodyRaw, $status, $headersArr, null, $full];
    };

    // === РАЗВИЛКА ПО ТИПУ ШЛЮЗА ===
    $requestDebug = [];
    $bodyUsed = null; $codeUsed = 0; $headersUsed = [];
    $wireForParser = null; // то, что передадим в generateNewResult()

    if (strcasecmp($gateType, 'MCMCN') === 0) {
        // --- НОВОЕ API (GET /api/get.dst_route) ---
        $endpoint = 'http://' . $host . ':8103/api/get.dst_route';

        $query = [
            'a_num'     => $item->src_number,   // отправитель
            'b_num'     => $item->dst_number,   // получатель
            'src_route' => $trunk->name,        // исходящий маршрут (имя транка)
            // 'trace'   => 'true',              // если на бекенде поддерживается — можно раскомментировать
        ];

        [$resp, $code, $hdrs, $err, $fullUrl] = $sendGet($endpoint, $query, [
            'Accept: application/json',
        ]);

        $requestDebug = [
            'endpoint'     => $endpoint,
            'http_code'    => $code,
            'headers'      => $hdrs,
            'payload_mode' => 'query',
            'query'        => $query,
            'full_url'     => $fullUrl ?? null,
        ];

        if ($resp === null) {
            throw new \yii\web\HttpException(502, 'Ошибка сети при обращении к внешнему API');
        }
        if ($code >= 407) {
            throw new \yii\web\HttpException(502, 'Внешний API вернул ошибку: HTTP ' . $code . ' — ' . mb_strimwidth($resp, 0, 800, '…'));
        }

        $bodyUsed    = $resp;
        $codeUsed    = $code;
        $headersUsed = $hdrs;

        // Приводим ответ MCMCN к формату, который понимает generateNewResult/processResult:
        // ожидается объект с полем 'trace' (массив шагов). Синтезируем минимально-достаточный след.
        $decoded = json_decode($bodyUsed, true);
        $dstRoute = $decoded['dst_route'] ?? '';
        $result   = isset($decoded['result']) ? (string)$decoded['result'] : '';
        $resultUp = strtoupper($result);

        $synthTrace = [
            // Можно добавить INFO-шаг с вводными — не обязателен
            [
                'type'    => 'INFO',
                'message' => sprintf('SMSC маршрутизация для a_num=%s b_num=%s src_route=%s', $item->src_number, $item->dst_number, $trunk->name),
                'path'    => 0,
            ],
            [
                'type'    => 'RESULT',
                'message' => sprintf('RESULT|%s|: %s', $resultUp ?: 'ACCEPT', $dstRoute),
                'path'    => 1,
            ],
        ];

        $wireForParser = json_encode(['trace' => $synthTrace], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        // --- СТАРОЕ API (POST /api/get.dst_route_smsc) ---
        $endpoint = 'http://' . $host . ':8103/api/get.dst_route_smsc';

        $payloadArr = [
            'trunk'  => $trunk->name,
            'caller' => $item->src_number,
            'called' => $item->dst_number,
            'trace'  => "true",
        ];
        $payloadJson = json_encode($payloadArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        [$resp1, $code1, $hdrs1] = $sendPostJson($endpoint, $payloadJson, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);

        $requestDebug = [
            'endpoint'     => $endpoint,
            'payload_mode' => 'json-object',
            'payload'      => $payloadArr,
            'http_code'    => $code1,
            'headers'      => $hdrs1,
        ];

        $bodyUsed    = $resp1;
        $codeUsed    = $code1;
        $headersUsed = $hdrs1;

        if ($bodyUsed === null) {
            throw new \yii\web\HttpException(502, 'Ошибка сети при обращении к внешнему API');
        }
        if ($codeUsed >= 407) {
            throw new \yii\web\HttpException(502, 'Внешний API вернул ошибку: HTTP ' . $codeUsed . ' — ' . mb_strimwidth($bodyUsed, 0, 800, '…'));
        }

        // Для старого API парсим как раньше — generateNewResult сам разберёт
        $wireForParser = $bodyUsed;
    }

    // Ключ кэша результата (как и раньше)
    $apiParams = [
        'user' => \Yii::$app->user->getId(),
        'date' => date('Y-m-d H:i:s'),
    ];
    $requestForKey = (isset($endpoint) ? rtrim($endpoint, '/') : '') . '?' . http_build_query($apiParams);
    $key = md5($requestForKey);

    // Парсим и сохраняем результат
    $result = $this->generateNewResult($wireForParser, $key);

    return [
        'item'   => $item->toArray(),
        'name'   => 'root',
        'key'    => $key,
        'result' => $result,
        'debug'  => $requestDebug,
    ];
}



    private function generateNewResult($resultString, $key)
{
    $resultString = str_replace(["\r", "\n", "\t"], "", $resultString);
    $tempResult   = json_decode($resultString, true);
    
    // Если trace — строка, раскодируем её, иначе оставляем как есть
    if (isset($tempResult['trace']) && is_string($tempResult['trace'])) {
        $tempResult['trace'] = json_decode($tempResult['trace'], true);
    }

    $result = $this->processResult($tempResult['trace'] ?? [], true);
    \Yii::$app->cache->set($key, $result);
    return $this->findByPath($result, '', 4);
}

}
