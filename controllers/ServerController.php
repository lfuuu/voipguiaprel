<?php

namespace app\controllers;

use app\classes\BaseController;
use app\models\ConfigVersion;
use yii\web\HttpException;
use app\models\Server;

class ServerController extends BaseController
{
    public function actionIndex($serverId)
    {
        $server = $this->getServerOr404($serverId);

        return $this->render('index', [
            'serverId' => $server->id,
            'versions' => ConfigVersion::find()->where(['server_id' => $server->id])->orderBy('updated_at desc')->all(),
        ]);
    }

    public function actionCreateConfig($serverId)
    {
        $server = $this->getServerOr404($serverId);

        $newVersion = new ConfigVersion();
        $newVersion->server_id = $server->id;
        $newVersion->name = 'Новая конфигурация';
        $newVersion->status_id = ConfigVersion::STATUS_DRAFT;
        $newVersion->updated_at = (new \DateTime())->format(\DateTime::ATOM);

        if (!$newVersion->save()) {
            throw new \Exception('Не удалось сохранить ConfigVersion');
        };

        return $this->redirect(['server/index', 'serverId' => $server->id]);
    }
}
