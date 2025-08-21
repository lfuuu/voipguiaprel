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

    /** Базовый хост внешнего сервиса */
    private const EXT_BASE = 'http://kannel2.mcn.ru:8085/v1';

    /** Универсальный вызов внешнего JSON API */
    private function callExternal(string $path, array $payload = null, string $method = 'POST'): array
    {
        $url = rtrim(self::EXT_BASE, '/') . '/' . ltrim($path, '/');
        $ch  = curl_init($url);

        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_TIMEOUT        => 5,
        ];

        if (strtoupper($method) === 'POST') {
            $opts[CURLOPT_POST]       = true;
            $opts[CURLOPT_POSTFIELDS] = $payload !== null ? json_encode($payload, JSON_UNESCAPED_UNICODE) : '{}';
        }

        curl_setopt_array($ch, $opts);

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error        = curl_error($ch);
        curl_close($ch);

        Yii::info(sprintf('[EXT] %s %s %s -> %s %s',
            $method, $url, $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE) : '',
            $httpCode, (string)$responseBody
        ), __METHOD__);

        if ($responseBody === false || $httpCode !== 200) {
            throw new \Exception("External API error (HTTP {$httpCode}) at {$url}: " . ($error ?: $responseBody));
        }

        $decoded = json_decode($responseBody, true);
        return is_array($decoded) ? $decoded : ['ok' => true];
    }

    // -------------------------------------------------
    // CRUD по самой сущности (как и было)
    // -------------------------------------------------
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

        return SmsTrunk::find()
            ->where(['sms_gate_id' => (int)$gateId])
            ->all();
    }

    public function actionSave()
    {
        if (isset($this->request['id'])) {
            $item = $this->getSmsOr404($this->request['id']);
        } else {
            $item = SmsTrunk::create();
        }

        $item->load($this->request, '');
        if (!$item->save()) {
            throw new FormValidationException($item);
        }

        return ['success' => 1, 'id' => $item->id];
    }

    // -------------------------------------------------
    // SMPP: add / modify / delete / get-list
    // -------------------------------------------------
    public function actionAddConfigurationTrunkSmpp()
    {
        $post = Yii::$app->request->post();

        $name = $post['name'] ?? null;
        if (!$name && !empty($post['trunk_id'])) {
            $trunk = SmsTrunk::findOne($post['trunk_id']);
            $name  = $trunk ? $trunk->name : null;
        }

        $payload = [
            'name'           => (string)$name,
            'host'           => $post['host']           ?? '',
            'port'           => $post['port']           ?? '',
            'smsc-username'  => $post['smsc-username']  ?? '',
            'smsc-password'  => $post['smsc-password']  ?? '',
        ];

        $this->callExternal('add_configuration_trunk_smpp', $payload, 'POST');
        return ['status' => 'ok'];
    }

    public function actionModifyConfigurationTrunkSmpp()
    {
        $post = Yii::$app->request->post();
        $payload = [
            'name'           => $post['name']           ?? '',
            'host'           => $post['host']           ?? '',
            'port'           => $post['port']           ?? '',
            'smsc-username'  => $post['smsc-username']  ?? '',
            'smsc-password'  => $post['smsc-password']  ?? '',
        ];

        $this->callExternal('modify_configuration_trunk_smpp', $payload, 'POST');
        return ['status' => 'ok'];
    }

    public function actionDeleteConfigurationTrunkSmpp()
    {
        $post = Yii::$app->request->post();
        $payload = [
            'name'           => $post['name']           ?? '',
            'host'           => $post['host']           ?? '',
            'port'           => $post['port']           ?? '',
            'smsc-username'  => $post['smsc-username']  ?? '',
            'smsc-password'  => $post['smsc-password']  ?? '',
        ];

        $this->callExternal('delete_configuration_trunk_smpp', $payload, 'POST');
        return ['status' => 'ok'];
    }

    public function actionGetConfigurationTrunksSmpp()
    {
        // Без тела, GET эквивалентно: шлём POST без payload тоже ок, но используем GET-поведение
        return $this->callExternal('get_configuration_trunks_smpp', null, 'POST');
    }

    // -------------------------------------------------
    // REST(API): add / modify / delete / get-list
    // -------------------------------------------------
    /**
     * Проксирует конфигурацию REST на внешний сервис через cURL
     */
    public function actionAddConfigurationTrunkApi()
    {
        $post   = Yii::$app->request->post();

        $name = $post['name'] ?? null;
        if (!$name && !empty($post['trunk_id'])) {
            $trunk = SmsTrunk::findOne($post['trunk_id']);
            $name  = $trunk ? $trunk->name : null;
        }

        $cfg = $post['config'] ?? [];
        $payload = [
            'name'                => (string)$name,
            'url'                 => $cfg['url']         ?? $post['url']         ?? '',
            'method'              => $cfg['method']      ?? $post['method']      ?? '',
            'contentType'         => $cfg['contentType'] ?? $post['contentType'] ?? '',
            // поле пишем строго как в спецификации: "autorization-token"
            'autorization-token'  => $cfg['authToken']   ?? $post['autorization-token'] ?? $post['authToken'] ?? '',
        ];

        $this->callExternal('add_configuration_trunk_api', $payload, 'POST');
        return ['status' => 'ok'];
    }

    public function actionModifyConfigurationTrunkApi()
    {
        $post = Yii::$app->request->post();
        $payload = [
            'name'                => $post['name']                ?? '',
            'url'                 => $post['url']                 ?? '',
            'method'              => $post['method']              ?? '',
            'contentType'         => $post['contentType']         ?? '',
            'autorization-token'  => $post['autorization-token']  ?? $post['authToken'] ?? '',
        ];

        $this->callExternal('modify_configuration_trunk_api', $payload, 'POST');
        return ['status' => 'ok'];
    }

    public function actionDeleteConfigurationTrunkApi()
    {
        $post = Yii::$app->request->post();
        $payload = [
            'name'                => $post['name']                ?? '',
            'url'                 => $post['url']                 ?? '',
            'method'              => $post['method']              ?? '',
            'contentType'         => $post['contentType']         ?? '',
            'autorization-token'  => $post['autorization-token']  ?? $post['authToken'] ?? '',
        ];

        $this->callExternal('delete_configuration_trunk_api', $payload, 'POST');
        return ['status' => 'ok'];
    }

    public function actionGetConfigurationTrunksApi()
    {
        return $this->callExternal('get_configuration_trunks_api', null, 'POST');
    }

    // -------------------------------------------------
    // RELOAD
    // -------------------------------------------------
    public function actionReloadConfiguration()
    {
        // Без параметров. Отправим POST с пустым телом.
        $this->callExternal('reload_configuration', [], 'POST');
        return ['status' => 'ok'];
    }

    // -------------------------------------------------
    // Вспомогательное
    // -------------------------------------------------
    private function getSmsOr404($id): SmsTrunk
    {
        $m = SmsTrunk::findOne((int)$id);
        if (!$m) {
            throw new \yii\web\NotFoundHttpException('SmsTrunk not found');
        }
        return $m;
    }
}
