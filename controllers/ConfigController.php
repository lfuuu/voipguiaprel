<?php

namespace app\controllers;

use app\classes\BaseController;
use app\classes\ConfigVersionCloner;
use app\models\ConfigVersion;
use Yii;

class ConfigController extends BaseController
{
    public $layout = false;

    public function actionIndex($versionId)
    {
        $version = $this->getVersionOr404($versionId);

        return $this->render('index', [
            'version' => $version,
        ]);
    }

    public function actionDelete($id)
    {
        $version = $this->getVersionOr404($id);

        $version->dao()->delete($version);

        return $this->redirect(['server/index', 'serverId' => $version->server_id]);
    }

    public function actionFix($id)
    {
        $version = $this->getVersionForUpdateOr404($id);

        $version->dao()->fix($version);

        return $this->redirect(['server/index', 'serverId' => $version->server_id]);
    }

    public function actionActivate($id)
    {
        $version = $this->getVersionOr404($id);
        if ($version->status_id != ConfigVersion::STATUS_PUBLISHED) {
            throw new \Exception(404, 'Активировать можно только зафиксированную версию');
        }

        $version->dao()->activate($version);

        return $this->redirect(['server/index', 'serverId' => $version->server_id]);
    }

    public function actionClone($id)
    {
        $version = $this->getVersionOr404($id);

        $cloner = ConfigVersionCloner::create($version);
        $newVersion = $cloner->cloneVersion();

        return $this->redirect(['config/index', 'versionId' => $newVersion->id]);
    }
}
