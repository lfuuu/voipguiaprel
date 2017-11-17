<?php

namespace app\controllers\json;

use app\models\Server;
use app\classes\JsonController;

class ServerController extends JsonController
{
    public function actionList()
    {
        return
            Server::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }
}
