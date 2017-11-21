<?php
namespace app\controllers;

use app\models\Acl;
use app\models\RoleAcl;
use Yii;
use app\classes\BaseController;
use app\forms\RoleForm;
use app\models\Role;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;

class RoleController extends BaseController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('role_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return $this->render('list', [
            'roles' => Role::find()->orderBy('name')->all(),
        ]);
    }

    public function actionCreate()
    {
        if (!\Yii::$app->user->can('role_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        \Yii::$app->user->can('edit_roles', ['profileId' => \Yii::$app->user->id]);
        
        $model = RoleForm::create('create');
    
        $aclPairs = $this->getAclPairs();
        
        if ($model->load(Yii::$app->request->post()) && $model->createRole()) {
            $roleAcl = Yii::$app->request->getBodyParam('roleAcl');
    
            $this->createRoleAcl($roleAcl, $model->name);
            
            return $this->redirect(['role/list']);
        } else {
            return $this->renderAjax('edit', [
                'model' => $model,
                'aclPairs' => $aclPairs,
                'roleAclPairs' => []
            ]);
        }
    }

    public function actionEdit($id)
    {
        if (!\Yii::$app->user->can('role_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $role = Role::findOne($id);

        $model = RoleForm::create('update');

        $model->name = $role->name;
        $model->description = $role->description;
    
        $aclPairs = $this->getAclPairs();
        $roleAclPairs = $this->getRoleAclPairs($model->name);
        
        if ($model->load(Yii::$app->request->post()) && $model->updateRole()) {
            $roleAcl = Yii::$app->request->getBodyParam('roleAcl');

            RoleAcl::deleteAll('parent = \'' . $model->name . '\'');

            $this->createRoleAcl($roleAcl, $model->name);

            return $this->redirect(['role/list']);
        } else {
            return $this->renderAjax('edit', [
                'model' => $model,
                'aclPairs' => $aclPairs,
                'roleAclPairs' => $roleAclPairs
            ]);
        }
    }
    
    private function getAclPairs()
    {
        $aclList = Acl::find()
            ->asArray()
            ->all();
    
        $formattedAclList = [];
    
        foreach ($aclList as $acl) {
            $formattedAclList[$acl['name']] = $acl['description'];
        }
    
        return $formattedAclList;
    }
    
    private function getRoleAclPairs($parent)
    {
        $roleAclList = RoleAcl::find()
            ->select('child')
            ->where('parent = \'' . $parent . '\'')
            ->asArray()
            ->all();
    
        $formattedRoleAclList = [];
    
        foreach ($roleAclList as $roleAcl) {
            $formattedRoleAclList[] = $roleAcl['child'];
        }
        
        return $formattedRoleAclList;
    }
    
    private function createRoleAcl($roleAcl, $parent)
    {
        if (!empty($roleAcl)) {
            foreach ($roleAcl as $aclId) {
                $data = [
                    'parent' => $parent,
                    'child' => $aclId
                ];
                $roleAclModel = RoleAcl::create($data);
                $roleAclModel->save();
            }
        }
    }

    public function actionDelete($name)
    {
        if (!\Yii::$app->user->can('role_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $model = RoleForm::create('delete');
        if ($model->load(['name'=>$name], '') && $model->deleteRole()) {
            return $this->redirect(['role/list']);
        } else {
            return $this->redirect(['role/list']);
        }
    }
}
