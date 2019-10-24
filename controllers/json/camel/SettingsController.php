<?php

namespace app\controllers\json\camel;

use app\classes\JsonController;

class SettingsController extends JsonController
{
    protected $modelName = 'app\models\ServerOcs';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $listPermission = 'camel_server_list';
    protected $editPermission = 'camel_server_edit';

    public function actionCreate()
    {
        // do_nothing
    }

    public function actionDelete()
    {
        // do_nothing
    }
}
