<?php

namespace app\controllers;

use app\classes\BaseController;
use \Yii;

class SmsController extends BaseController
{
    public function actionIndex($serverId)
    {

        $server = $this->getServerOcsOr404($serverId);

        return $this->render('index', [
            'serverId' => $server->id,
        ]);
    }
}