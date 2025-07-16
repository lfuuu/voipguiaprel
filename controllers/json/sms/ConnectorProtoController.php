<?php
// controllers/json/sms/ConnectorProtoController.php

namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\models\auth\SmsConnectorProto;

class ConnectorProtoController extends JsonController
{
    protected $modelName     = SmsConnectorProto::class;
    protected $idParamName   = 'id';
    protected $nameParamName = 'type';
    protected $readWhere     = [];

    /**
     * Список всех протоколов коннекторов
     */
    public function actionRead()
    {
        return SmsConnectorProto::find()->all();
    }
}
