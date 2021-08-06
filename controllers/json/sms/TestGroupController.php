<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;

class TestGroupController extends JsonController
{
    protected $modelName = 'app\models\auth\SmsTestGroup';
    protected $idParamName = 'id';
    protected $nameParamName = 'group_name';
    protected $createPermission = 'sms_test_group_create';
    protected $listPermission = 'sms_test_group_list';
    protected $editPermission = 'sms_test_group_edit';
    protected $deletePermission = 'sms_test_group_delete';
}
