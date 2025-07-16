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

    public function actionRead()
    {
        return SmsTrunk::find()->all();
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

   public function actionAddConfigurationTrunkSmpp()
{
    $post    = Yii::$app->request->post();
    $trunkId = $post['trunk_id'];
    $name    = $post['name'] ?? SmsTrunk::findOne($trunkId)->name;

    $payload = [
        'name'            => $name,
        'host'            => $post['host'],
        'port'            => $post['port'],
        'smsc-username'   => $post['smsc-username'],
        'smsc-password'   => $post['smsc-password'],
    ];

    Yii::info("SMPP proxy payload: " . json_encode($payload), __METHOD__);

    $ch = curl_init('http://10.252.0.87:8085/v1/add_configuration_trunk_smpp');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 5,
    ]);

    $responseBody = curl_exec($ch);
    $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error        = curl_error($ch);
    curl_close($ch);

    Yii::info("SMPP proxy response {$httpCode}: {$responseBody}", __METHOD__);

    if ($responseBody === false || $httpCode !== 200) {
        throw new \Exception(
            "External SMPP API error (HTTP {$httpCode}): " .
            ($error ?: $responseBody)
        );
    }

    return ['status' => 'ok'];
}



    /**
     * Проксирует конфигурацию REST на внешний сервис через cURL
     */
    public function actionAddConfigurationTrunkApi()
    {
        $post   = Yii::$app->request->post();
        $config = $post['config'];
        $trunk  = SmsTrunk::findOne($post['trunk_id']);

        $payload = json_encode([
'name' => $post['name'] ?? SmsTrunk::findOne($trunkId)->name,
            'url'                 => $config['url'],
            'method'              => $config['method'],
            'contentType'         => $config['contentType'],
            'autorization-token'  => $config['authToken'],
        ]);

        $url = 'http://10.252.0.87:8085/v1/add_configuration_trunk_api';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST,         true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,   ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS,   $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT,      5);

        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($response === false || $code !== 200) {
            throw new \Exception("External REST API error (HTTP $code): $err");
        }

        return ['status' => 'ok'];
    }
}
