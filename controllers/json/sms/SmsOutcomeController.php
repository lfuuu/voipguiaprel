<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;

class SmsOutcomeController extends JsonController
{
    protected $modelName = 'app\models\auth\SmsOutcome';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $readWhere = ['server_id'];
    protected $createPermission = 'sms_outcome_create';
    protected $listPermission = 'sms_outcome_list';
    protected $editPermission = 'sms_outcome_edit';
    protected $deletePermission = 'sms_outcome_delete';
}