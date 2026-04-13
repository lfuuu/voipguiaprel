<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\auth\SmsTrunk;
use Yii;

class SmsController extends JsonController
{
    protected $modelName        = SmsTrunk::class;
    protected $idParamName      = 'id';
    protected $nameParamName    = 'name';
    protected $readWhere        = ['server_id'];
    protected $createPermission = 'sms_trunk_create';
    protected $listPermission   = 'sms_trunk_list';
    protected $editPermission   = 'sms_trunk_edit';
    protected $deletePermission = 'sms_trunk_delete';

    /**
     * База внешнего сервиса с учётом региона:
     *   EU  → https://smsgate-api.kompaas.tech
     *   RU  → https://smsgate-api.mcn.ru
     */
    private function getExtBase(): string
    {
        // 1) Жёсткое переопределение через params, если нужно
        if (!empty(Yii::$app->params['kannelBase'])) {
            return rtrim(Yii::$app->params['kannelBase'], '/');
        }

        // 2) Автовыбор по isEuropean
        $isEu = Yii::$app->params['isEuropean'] ?? false;

        if ($isEu) {
            return 'https://smsgate-api.kompaas.tech';
        }

        return 'https://smsgate-api.mcn.ru';
    }

    /** Универсальный вызов внешнего HTTP JSON API */
    private function httpCall(string $method, string $path, ?array $payload = null): array
    {
        $base = $this->getExtBase();
        $url  = rtrim($base, '/') . '/' . ltrim($path, '/');

        $ch  = curl_init($url);
        $hdr = ['Accept: application/json', 'Content-Type: application/json'];

        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,   // общий таймаут, пусть остаётся 15 секунд
            CURLOPT_CONNECTTIMEOUT => 1,    // 1 секунда = 1000 мс
            CURLOPT_HTTPHEADER     => $hdr,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_HEADER         => false,
        ];


        if ($payload !== null) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_UNESCAPED_UNICODE);
        }

        curl_setopt_array($ch, $opts);

        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        Yii::info(sprintf('[EXT %s] %s%s%s -> %s %s',
            $method,
            $url,
            $payload ? ' ' : '',
            $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE) : '',
            $code,
            (string)$body
        ), __METHOD__);

        if ($body === false || $code < 200 || $code >= 300) {
            throw new \Exception("External API error (HTTP {$code}) at {$url}: " . ($err ?: $body));
        }

        $decoded = json_decode((string)$body, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function buildSmppPayload(array $post, ?string $fallbackName = null): array
    {
        $name = $post['name'] ?? $fallbackName;

        $payload = [
            'name'              => (string)$name,
            'host'              => $post['host'] ?? '',
            'port'              => $post['port'] ?? '',
            'smsc-username'     => $post['smsc-username'] ?? '',
            'smsc-password'     => $post['smsc-password'] ?? '',
            'use-ssl'           => isset($post['use-ssl']) ? (bool)$post['use-ssl'] : false,
            'transceiver-mode'  => isset($post['transceiver-mode']) ? (bool)$post['transceiver-mode'] : false,
        ];

        if (!empty($post['system-type'])) {
            $payload['system-type'] = (string)$post['system-type'];
        }

        $sourceAddr = $post['source-addr'] ?? null;
        if ($sourceAddr === null && (isset($post['source-addr-ton']) || isset($post['source-addr-npi']))) {
            $sourceAddr = [
                'ton' => $post['source-addr-ton'] ?? null,
                'npi' => $post['source-addr-npi'] ?? null,
            ];
        }

        if ($sourceAddr !== null) {
            $ton = is_array($sourceAddr) ? trim((string)($sourceAddr['ton'] ?? '')) : '';
            $npi = is_array($sourceAddr) ? trim((string)($sourceAddr['npi'] ?? '')) : '';

            if ($ton === '' xor $npi === '') {
                throw new \InvalidArgumentException('source-addr must contain both ton and npi.');
            }

            if ($ton !== '' && $npi !== '') {
                $payload['source-addr'] = [
                    'ton' => $ton,
                    'npi' => $npi,
                ];
            }
        }

        return $payload;
    }

    private function normalizeSmppConfig(array $config): array
    {
        unset($config['allowed-smsc-id'], $config['allowedSmscId'], $config['smsc']);

        if (array_key_exists('transceiver-mode', $config)) {
            $config['transceiver-mode'] = (bool)$config['transceiver-mode'];
        } elseif (array_key_exists('transceiverMode', $config)) {
            $config['transceiver-mode'] = (bool)$config['transceiverMode'];
        }

        $sourceAddr = $config['source-addr'] ?? ($config['sourceAddr'] ?? null);
        if (is_array($sourceAddr)) {
            $ton = trim((string)($sourceAddr['ton'] ?? ''));
            $npi = trim((string)($sourceAddr['npi'] ?? ''));

            if ($ton !== '' && $npi !== '') {
                $config['source-addr'] = [
                    'ton' => $ton,
                    'npi' => $npi,
                ];
            } else {
                unset($config['source-addr']);
            }
        }

        unset($config['sourceAddr']);

        return $config;
    }

    // -------------------- CRUD локальной модели --------------------

    public function actionRead()
    {
        return SmsTrunk::find()->all();
    }

    public function actionReadByGate()
    {
        $gateId = $this->request['sms_gate_id'] ?? null;
        if ($gateId === null) {
            return ['success' => 0, 'error' => 'Parameter sms_gate_id is required'];
        }

        return SmsTrunk::find()->where(['sms_gate_id' => (int)$gateId])->all();
    }

    public function actionSave()
    {
        if (isset($this->request['id'])) {
            $item = $this->getSmsOr404($this->request['id']); // наследуется из BaseController
        } else {
            $item = SmsTrunk::create();
        }

        $item->load($this->request, '');

        // дублируем route_name из name, если не передан
        if (empty($item->route_name)) {
            $item->route_name = $item->name;
        }

        if (!$item->save()) {
            throw new FormValidationException($item);
        }

        return ['success' => 1, 'id' => $item->id];
    }

    // -------------------- Внешние API: SMPP --------------------

    /** GET список SMPP транков */
    public function actionGetConfigurationTrunksSmpp()
    {
        // /v1/trunks/smpp (GET)
        $response = $this->httpCall('GET', '/v1/trunks/smpp');

        return array_map(function ($item) {
            return is_array($item) ? $this->normalizeSmppConfig($item) : [];
        }, $response);
    }

    /** POST создать SMPP транк */
    public function actionAddConfigurationTrunkSmpp()
    {
        $post    = Yii::$app->request->post();
        $trunkId = $post['trunk_id'] ?? null;
        $name    = $post['name'] ?? ($trunkId ? (SmsTrunk::findOne($trunkId)->name ?? null) : null);

        $payload = $this->buildSmppPayload($post, $name);

        $this->httpCall('POST', '/v1/trunks/smpp', $payload);
        return ['status' => 'ok'];

    }

    /** PUT изменить SMPP транк по trunk_id */
    public function actionModifyConfigurationTrunkSmpp()
    {
        $post      = Yii::$app->request->post();
        $configId  = $post['config_id'] ?? $post['id'] ?? null;
        $name      = $post['name'] ?? null;

        if (empty($configId)) {
            // если нет config_id → ищем по name
            if (empty($name) && !empty($post['trunk_id'])) {
                $trunk = \app\models\auth\SmsTrunk::findOne((int)$post['trunk_id']);
                $name  = $trunk ? $trunk->name : null;
            }
            if (empty($name)) {
                throw new \InvalidArgumentException('modify SMPP: provide config_id or name.');
            }

            $list = $this->httpCall('GET', '/v1/trunks/smpp');
            foreach ((array)$list as $row) {
                if (isset($row['name']) && (string)$row['name'] === (string)$name) {
                    $configId = $row['id'] ?? null;
                    break;
                }
            }
            if (empty($configId)) {
                throw new \RuntimeException("modify SMPP: config id not found by name '{$name}'.");
            }
        }

        // формируем тело без id/trunk_id
        $payload = $this->buildSmppPayload($post);

        $this->httpCall('PUT', "/v1/trunks/smpp/{$configId}", $payload);
        return ['status' => 'ok', 'config_id' => (int)$configId];
    }

    /** DELETE удалить SMPP транк по trunk_id */
    public function actionDeleteConfigurationTrunkSmpp()
    {
        $post      = Yii::$app->request->post();
        $configId  = $post['config_id'] ?? $post['id'] ?? null; // предпочтительно
        $name      = $post['name'] ?? null;

        // если нет config_id — пробуем вычислить по name
        if (empty($configId)) {
            // если нет name, но есть trunk_id — возьмём name из локальной БД
            if (empty($name) && !empty($post['trunk_id'])) {
                $trunk = \app\models\auth\SmsTrunk::findOne((int)$post['trunk_id']);
                if ($trunk && $trunk->name) {
                    $name = $trunk->name;
                }
            }
            if (empty($name)) {
                throw new \InvalidArgumentException('delete SMPP: provide config_id (preferred) or name (or trunk_id to resolve name).');
            }

            // тянем список и ищем id по name
            $list = $this->httpCall('GET', '/v1/trunks/smpp');
            foreach ((array)$list as $row) {
                if (isset($row['name']) && (string)$row['name'] === (string)$name) {
                    $configId = $row['id'] ?? null;
                    break;
                }
            }
            if (empty($configId)) {
                throw new \RuntimeException("delete SMPP: config id not found by name '{$name}'.");
            }
        }

        // DELETE без тела
        $this->httpCall('DELETE', "/v1/trunks/smpp/{$configId}", null);
        return ['status' => 'ok', 'config_id' => (int)$configId];
    }

    // -------------------- Внешние API: REST(API) --------------------

    /** GET список REST/API транков */
    public function actionGetConfigurationTrunksApi()
    {
        // /v1/trunks/api (GET)
        return $this->httpCall('GET', '/v1/trunks/api');
    }

    /** POST создать REST/API транк */
    public function actionAddConfigurationTrunkApi()
    {
        $post    = Yii::$app->request->post();
        $trunkId = $post['trunk_id'] ?? null;
        $name    = $post['name'] ?? ($trunkId ? (SmsTrunk::findOne($trunkId)->name ?? null) : null);

        // данные могут прийти как в корне, так и внутри config
        $cfg = $post['config'] ?? $post;

        $payload = [
            'name'               => (string)$name,
            'url'                => $cfg['url'] ?? '',
            'method'             => $cfg['method'] ?? '',
            'contentType'        => $cfg['contentType'] ?? '',
            'autorization-token' => $cfg['authToken'] ?? ($cfg['autorization-token'] ?? ''),
        ];

        $this->httpCall('POST', '/v1/trunks/api', $payload);
        return ['status' => 'ok'];
    }

    /** PUT изменить REST/API транк по trunk_id */
    public function actionModifyConfigurationTrunkApi()
    {
        $post      = Yii::$app->request->post();
        $configId  = $post['config_id'] ?? $post['id'] ?? null;
        $name      = $post['name'] ?? null;

        if (empty($configId)) {
            if (empty($name) && !empty($post['trunk_id'])) {
                $trunk = \app\models\auth\SmsTrunk::findOne((int)$post['trunk_id']);
                $name  = $trunk ? $trunk->name : null;
            }
            if (empty($name)) {
                throw new \InvalidArgumentException('modify API: provide config_id or name.');
            }

            $list = $this->httpCall('GET', '/v1/trunks/api');
            foreach ((array)$list as $row) {
                if (isset($row['name']) && (string)$row['name'] === (string)$name) {
                    $configId = $row['id'] ?? null;
                    break;
                }
            }
            if (empty($configId)) {
                throw new \RuntimeException("modify API: config id not found by name '{$name}'.");
            }
        }

        $payload = [
            'name'               => $post['name'],
            'url'                => $post['url'],
            'method'             => $post['method'],
            'contentType'        => $post['contentType'],
            'autorization-token' => $post['autorization-token'],
        ];

        $this->httpCall('PUT', "/v1/trunks/api/{$configId}", $payload);
        return ['status' => 'ok', 'config_id' => (int)$configId];
    }

    /** DELETE удалить REST/API транк по trunk_id */
    public function actionDeleteConfigurationTrunkApi()
    {
        $post      = Yii::$app->request->post();
        $configId  = $post['config_id'] ?? $post['id'] ?? null; // предпочтительно
        $name      = $post['name'] ?? null;

        if (empty($configId)) {
            if (empty($name) && !empty($post['trunk_id'])) {
                $trunk = \app\models\auth\SmsTrunk::findOne((int)$post['trunk_id']);
                if ($trunk && $trunk->name) {
                    $name = $trunk->name;
                }
            }
            if (empty($name)) {
                throw new \InvalidArgumentException('delete API: provide config_id (preferred) or name (or trunk_id to resolve name).');
            }

            $list = $this->httpCall('GET', '/v1/trunks/api');
            foreach ((array)$list as $row) {
                if (isset($row['name']) && (string)$row['name'] === (string)$name) {
                    $configId = $row['id'] ?? null;
                    break;
                }
            }
            if (empty($configId)) {
                throw new \RuntimeException("delete API: config id not found by name '{$name}'.");
            }
        }

        $this->httpCall('DELETE', "/v1/trunks/api/{$configId}", null);
        return ['status' => 'ok', 'config_id' => (int)$configId];
    }
}
