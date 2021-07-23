<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;
use yii\web\ForbiddenHttpException;

class SmsController extends JsonController
{
    protected $modelName = 'app\models\auth\SmsTrunk';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $readWhere = ['server_id'];
    protected $createPermission = 'camel_trunk_create';
    protected $listPermission = 'camel_trunk_list';
    protected $editPermission = 'camel_trunk_edit';
    protected $deletePermission = 'camel_trunk_delete';

    public function actionRead()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $items =
            $modelName::find()
                ->all();

        return $items;
    }
}