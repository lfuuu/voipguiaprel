<?php

namespace app\controllers\json\camel;

use app\classes\JsonController;

class TestAuthController extends JsonController
{
    protected $modelName = 'app\models\auth\CamelTestAuth';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $readWhere = ['server_id'];
    protected $createPermission = 'camel_test_auth_create';
    protected $listPermission = 'camel_test_auth_list';
    protected $editPermission = 'camel_test_auth_edit';
    protected $deletePermission = 'camel_test_auth_delete';

    public function actionResult()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $item = $modelName::findOne($this->request['id']);

        if ($item === null) {
            throw new HttpException(404, $this->modelName . ' не найден');
        }

        $apiUrl = 'http://reg99.mcntelecom.ru:8101/';
        $apiParams = [
            'a_number' => $item->a_number,
            'b_number' => $item->b_number,
            'gt_number' => $item->gt_number,
            'with_debug_info' => $item->with_debug_info ? 1 : 0,
            'camel_trunk_name' => $item->camel_trunk_name,
            'server_id' => $item->server_id,
        ];

        $request = $apiUrl . 'api/camel?' . http_build_query($apiParams);

        $response = file_get_contents($request);

        return 1;
    }
}
