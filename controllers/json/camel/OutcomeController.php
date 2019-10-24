<?php

namespace app\controllers\json\camel;

use app\classes\JsonController;

class OutcomeController extends JsonController
{
    protected $modelName = 'app\models\auth\CamelOutcome';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $readWhere = ['server_id'];
    protected $createPermission = 'camel_outcome_create';
    protected $listPermission = 'camel_outcome_list';
    protected $editPermission = 'camel_outcome_edit';
    protected $deletePermission = 'camel_outcome_delete';
}
