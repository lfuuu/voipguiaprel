<?php

namespace app\controllers\json\settings;

use app\classes\JsonController;

class AclController extends JsonController
{
    protected $modelName = 'app\models\Acl';
    protected $idParamName = 'name';
    protected $nameParamName = 'description';
    protected $listPermission = 'acl_list';

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
