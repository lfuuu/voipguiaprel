<?php

namespace app\controllers\json\api_billing;

use app\classes\JsonController;

class ApiMethodController extends JsonController
{
    protected $modelName = 'app\models\billing_api\ApiMethod';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $createPermission = 'api_billing_api_method_create';
    protected $listPermission = 'api_billing_api_method_list';
    protected $editPermission = 'api_billing_api_method_edit';
    protected $deletePermission = 'api_billing_api_method_delete';

    public function actionRead()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $items =
            $modelName::find()
                ->alias('am')
                ->select('am.*')
                ->innerJoin('billing_api.api a', 'a.id = am.api_id')
                ->orderBy('am.' . $this->nameParamName)
                ->where(['a.server_id' => $this->request['server_id']])
                ->asArray()
                ->all();

        $items = $this->performAfterReadActions($items);

        return $items;
    }
}
