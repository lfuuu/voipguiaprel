<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\billing_uu\A2pAlphaNumbers;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class A2pAlphaNumbersController extends JsonController
{
    protected $modelName = 'app\models\billing_uu\A2pAlphaNumbers';
    protected $idParamName = 'id';
    protected $nameParamName = 'alphanum';
    protected $createPermission = 'sms_test_group_create';
    protected $listPermission = 'sms_test_group_list';
    protected $editPermission = 'sms_test_group_edit';
    protected $deletePermission = 'sms_test_group_delete';
}