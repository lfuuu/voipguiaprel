<?php

namespace app\controllers;

use app\classes\BaseController;

class ServerController extends BaseController
{
    public function actionIndex($serverId)
    {
        $server = $this->getServerOr404($serverId);

        return $this->render('index', [
            'serverId' => $server->id,
        ]);
    }
}
