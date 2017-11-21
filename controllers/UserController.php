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
            'users' => User::find()->orderBy('name')->all(),
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
            $role = Yii::$app->request->post()['UserForm']['role'];

            $this->createUserAcl($role, $model->id);
            
            return $this->redirect(['user/list']);
        } else {
            return $this->render('edit', [
                'model' => $model,
                'rolePairs' => $rolePairs
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
        $auth = Yii::$app->authManager;
        
        $role = array_pop($auth->getRolesByUser($user->id));
        
        $model->role = $role ? $role->name : '';
    
        $rolePairs = $this->getRolePairs();

        if ($model->load(Yii::$app->request->post()) && $model->updateUser()) {
            $role = Yii::$app->request->post()['UserForm']['role'];
            
            UserAcl::deleteAll('user_id = \'' . $model->id . '\'');
    
            $this->createUserAcl($role, $model->id);
            
            return $this->redirect(['user/list']);
        } else {
            return $this->render('edit', [
                'model' => $model,
                'rolePairs' => $rolePairs
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
    
    private function createUserAcl($role, $userId)
    {
        $auth = Yii::$app->authManager;
        
        $roleObject = $auth->getRole($role);
        
        $auth->assign($roleObject, $userId);
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
