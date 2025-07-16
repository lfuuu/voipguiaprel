<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\models\auth\SmsConnectorType;

class ConnectorTypeController extends JsonController
{
    protected $modelName     = SmsConnectorType::class;
    protected $idParamName   = 'id';
    protected $nameParamName = 'type';
    protected $readWhere     = [];

    /**
     * Список всех типов коннекторов без проверок прав
     */
    public function actionRead()
    {
        return SmsConnectorType::find()->all();
    }
}
