<?php

namespace app\controllers\json;

use app\models\NetworkConfig;
use Yii;
use app\classes\JsonController;

class NetworkController extends JsonController
{

    public function actionList() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            NetworkConfig::find()
                ->select(['id', 'name'])
                ->where(['instance_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }
}
