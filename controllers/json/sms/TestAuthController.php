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

    $isEuropean = \Yii::$app->params['isEuropean'] ?? false;

    if ($isEuropean) {
    // Жёсткий EU endpoint
    $endpoint = 'http://ocslte1.kompaas.tech:8103/api/get.dst_route_smsc';

    // ЛОГИРОВАНИЕ ДЛЯ ЕВРОПЕЙСКОГО СЛУЧАЯ
    \Yii::info(sprintf(
        '[SmsTestAuth][EU] endpoint resolved: %s | server_id=%s | test_id=%s | is_reserve=%s',
        $endpoint,
        (string)$server->id,
        (string)$item->id,
        var_export($this->request['is_reserve'] ?? null, true)
    ), __METHOD__);
    } else {
        $isReserve = (!empty($this->request['is_reserve']) && $this->request['is_reserve'] === true);
        $baseUrl   = $isReserve ? $server->camel_reserve : $server->camel_gw;
        $parsed    = parse_url($baseUrl);
        $host      = $parsed['host'] ?? ($parsed['path'] ?? $baseUrl);
        $host      = preg_replace('~^https?://~i', '', (string)$host);
        $host      = preg_replace('~/.*$~', '', $host);
        $endpoint  = 'http://' . $host . ':8103/api/get.dst_route_smsc';
    }

    $trunk = \app\models\auth\SmsTrunk::findOne(['id' => $item->trunk_name]);
    if ($trunk === null) {
        throw new \yii\web\HttpException(404, 'Транк не найден');
    }

    $payloadArr = [
        'trunk'  => $trunk->name,
        'caller' => $item->src_number,
        'called' => $item->dst_number,
        'trace'  => "true",
    ];
    $payloadJson = json_encode($payloadArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $send = function (string $body, array $headers) use ($endpoint) {
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_POST              => true,
            CURLOPT_POSTFIELDS        => $body,
            CURLOPT_HTTPHEADER        => $headers,
            CURLOPT_HEADER            => true,   // чтобы вытащить заголовки/код
            CURLOPT_TIMEOUT_MS        => 15000,  // общий таймаут = 15 сек
            CURLOPT_CONNECTTIMEOUT_MS => 1000,   // коннект = 1000 мс
        ]);
        $raw      = curl_exec($ch);
        $errno    = curl_errno($ch);
        $errstr   = curl_error($ch);
        $status   = 0;
        $hdrSize  = 0;
        if ($raw !== false) {
            $status  = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $hdrSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        }
        curl_close($ch);
        if ($raw === false) {
            return [null, $status, [], $errno . ':' . $errstr];
        }
        $headersRaw = substr($raw, 0, $hdrSize);
        $bodyRaw    = substr($raw, $hdrSize);
        $headersArr = preg_split("/\r\n|\n|\r/", trim($headersRaw));
        return [$bodyRaw, $status, $headersArr, null];
    };

    [$resp1, $code1, $hdrs1, $err1] = $send($payloadJson, [
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

    $bodyUsed  = $resp1;
    $codeUsed  = $code1;
    $headersUsed = $hdrs1;

    $oatppParseFail =
        (is_string($resp1) && stripos($resp1, 'preparseString') !== false)
        || (is_string($resp1) && stripos($resp1, 'expected') !== false);

    if ($code1 >= 400 && $oatppParseFail) {
        // Попытка №2: двукратная сериализация — тело это строка, в которой лежит JSON
        $payloadStringified = json_encode($payloadJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        [$resp2, $code2, $hdrs2, $err2] = $send($payloadStringified, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);

        $requestDebug = [
            'endpoint'       => $endpoint,
            'payload_mode'   => 'json-string',      // ВАЖНО: "JSON как строка"
            'payload_string' => $payloadJson,       // что именно завернули в строку
            'http_code'      => $code2,
            'headers'        => $hdrs2,
            'first_attempt'  => [
                'http_code' => $code1,
                'body'      => mb_strimwidth((string)$resp1, 0, 500, '…'),
            ],
        ];
        $bodyUsed    = $resp2;
        $codeUsed    = $code2;
        $headersUsed = $hdrs2;
    }

    if ($bodyUsed === null) {
        throw new \yii\web\HttpException(502, 'Ошибка сети при обращении к внешнему API');
    }
    if ($codeUsed >= 400) {
        throw new \yii\web\HttpException(502, 'Внешний API вернул ошибку: HTTP ' . $codeUsed . ' — ' . mb_strimwidth($bodyUsed, 0, 800, '…'));
    }

    // Ключ кэша результата (как и раньше)
    $apiParams = [
        'user' => \Yii::$app->user->getId(),
        'date' => date('Y-m-d H:i:s'),
    ];
    $requestForKey = rtrim($endpoint, '/') . '?' . http_build_query($apiParams);
    $key = md5($requestForKey);

    // Парсим и сохраняем результат
    $result = $this->generateNewResult($bodyUsed, $key);

    return [
        'item'         => $item->toArray(),
        'name'         => 'root',
        'key'          => $key,
        'result'       => $result,
        'debug'        => $requestDebug, // очень полезно на стенде
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
