<?php

namespace app\controllers\json\settings;

use app\classes\JsonController;
use app\models\RoleAcl;

class RoleController extends JsonController
{
    protected $modelName = 'app\models\Role';
    protected $idParamName = 'name';
    protected $nameParamName = 'description';
    protected $withDependencies = ['roleAcl'];
    protected $throwExceptionOnEmptyItemInSave = false;
    protected $createPermission = 'role_create';
    protected $listPermission = 'role_list';
    protected $editPermission = 'role_edit';
    protected $deletePermission = 'role_delete';

    protected function performAfterGetActions($item)
    {
        $roleAclArray = [];

        foreach ($item['roleAcl'] as $roleAcl) {
            $roleAclArray[] = $roleAcl['child'];
        }

        $item['roleAcl'] = $roleAclArray;

        return $item;
    }

    protected function performAfterSaveActions($item, $request)
    {
        RoleAcl::deleteAll('parent = \'' . $item['name'] . '\'');

        if (!empty($request['roleAcl'])) {
            foreach ($request['roleAcl'] as $aclId) {
                $data = [
                    'parent' => $request['name'],
                    'child' => $aclId
                ];
                $roleAclModel = RoleAcl::create($data);
                $roleAclModel->save();
            }
        }
    }
}
