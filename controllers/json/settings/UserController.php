<?php

namespace app\controllers\json\settings;

use app\classes\JsonController;
use app\models\UserAcl;

class UserController extends JsonController
{
    protected $modelName = 'app\models\User';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $withDependencies = ['assignment'];
    protected $createPermission = 'user_create';
    protected $listPermission = 'user_list';
    protected $editPermission = 'user_edit';
    protected $deletePermission = 'user_delete';

    protected function performAfterReadActions($items)
    {
        foreach ($items as &$item) {
            unset($item['password_hash']);
        }

        return $items;
    }

    protected function performAfterGetActions($item)
    {
        unset($item['password_hash']);

        $assignmentArray = [];

        foreach ($item['assignment'] as $assignment) {
            $assignmentArray[] = $assignment['item_name'];
        }

        $item['assignment'] = $assignmentArray;

        return $item;
    }

    protected function performBeforeSaveActions($item, $request)
    {
        if (!empty($request['password'])) {
            $item->setPassword($request['password']);
        }
    }

    protected function performAfterSaveActions($item, $request)
    {
        $roles = $request['assignment'];

        UserAcl::deleteAll('user_id = \'' . $item->id . '\'');

        foreach ($roles as $role) {
            $auth = \Yii::$app->authManager;

            $roleObject = $auth->getRole($role);

            $auth->assign($roleObject, $item->id);
        }
    }
}
