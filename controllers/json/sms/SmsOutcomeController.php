<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;

class SmsOutcomeController extends JsonController
{
    protected $modelName = 'app\models\auth\SmsOutcome';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $readWhere = ['server_id'];
    protected $createPermission = 'camel_outcome_create';
    protected $listPermission = 'camel_outcome_list';
    protected $editPermission = 'camel_outcome_edit';
    protected $deletePermission = 'camel_outcome_delete';
}