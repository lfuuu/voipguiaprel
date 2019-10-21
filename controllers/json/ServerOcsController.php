<?php

namespace app\controllers\json;

use app\classes\JsonController;

class ServerOcsController extends JsonController
{
    protected $modelName = 'app\models\ServerOcs';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $listPermission = 'server_ocs_list';

    public function actionGet()
    {
        // do_nothing
    }

    public function actionSave()
    {
        // do_nothing
    }

    public function actionDelete()
    {
        // do_nothing
    }
}
