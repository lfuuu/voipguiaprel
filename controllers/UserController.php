<?php
namespace app\controllers;

use app\models\Role;
use app\models\UserAcl;
use Yii;
use app\classes\BaseController;
use app\forms\UserForm;
use app\models\User;
use yii\web\ForbiddenHttpException;

class UserController extends BaseController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('user_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return $this->render('list', [
            'users' => User::find()
                ->with('assignment.description')
                ->orderBy('name')
                ->all(),
        ]);
    }

    public function actionCreate()
    {
        if (!\Yii::$app->user->can('user_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $model = UserForm::create('create');
        
        $rolePairs = $this->getRolePairs();
        
        if ($model->load(Yii::$app->request->post()) && $model->createUser()) {
            $userRole = Yii::$app->request->getBodyParam('userRole');
    
            $this->createUserAcl($userRole, $model->id);
            
            return $this->redirect(['user/list']);
        } else {
            return $this->renderAjax('edit', [
                'model' => $model,
                'rolePairs' => $rolePairs,
                'userRolePairs' => []
            ]);
        }
    }

    public function actionEdit($id)
    {
        if (!\Yii::$app->user->can('user_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $user = User::findOne($id);

        $model = UserForm::create('update');

        $model->id = $user->id;
        $model->login = $user->login;
        $model->name = $user->name;
        
        $rolePairs = $this->getRolePairs();
        $userRolePairs = $this->getUserRolePairs($model->id);

        if ($model->load(Yii::$app->request->post()) && $model->updateUser()) {
            $userRole = Yii::$app->request->getBodyParam('userRole');
            
            UserAcl::deleteAll('user_id = \'' . $model->id . '\'');
    
            $this->createUserAcl($userRole, $model->id);
            
            return $this->redirect(['user/list']);
        } else {
            return $this->renderAjax('edit', [
                'model' => $model,
                'rolePairs' => $rolePairs,
                'userRolePairs' => $userRolePairs
            ]);
        }
    }
    
    private function getRolePairs()
    {
        $roleList = Role::find()
            ->asArray()
            ->all();
        
        $formattedRoleList = [];
        
        foreach ($roleList as $role) {
            $formattedRoleList[$role['name']] = $role['description'];
        }
        
        return $formattedRoleList;
    }
    
    private function getUserRolePairs($userId)
    {
        $list = UserAcl::find()
            ->select('item_name')
            ->where('user_id = :user_id')
            ->addParams([':user_id' => $userId])
            ->asArray()
            ->all();
        
        $formattedList = [];
        
        foreach ($list as $item) {
            $formattedList[] = $item['item_name'];
        }
        
        return $formattedList;
    }
    
    private function createUserAcl($roles, $userId)
    {
        foreach ($roles as $role) {
            $auth = Yii::$app->authManager;
    
            $roleObject = $auth->getRole($role);
    
            $auth->assign($roleObject, $userId);
        }
    }

    public function actionDelete($id)
    {
        if (!\Yii::$app->user->can('user_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $model = UserForm::create('delete');
        if ($model->load(['id'=>$id], '') && $model->deleteUser()) {
            return $this->redirect(['user/list']);
        } else {
            return $this->redirect(['user/list']);
        }
    }
}
