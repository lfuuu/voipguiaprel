<?php
namespace app\controllers;

use Yii;
use app\classes\BaseController;
use app\forms\UserForm;
use app\models\User;

class UserController extends BaseController
{
    public function actionList()
    {
        return $this->render('list', [
            'users' => User::find()->orderBy('name')->all(),
        ]);
    }

    public function actionCreate()
    {
        $model = UserForm::create('create');
        if ($model->load(Yii::$app->request->post()) && $model->createUser()) {
            return $this->redirect(['user/list']);
        } else {
            return $this->render('edit', [
                'model' => $model,
            ]);
        }
    }

    public function actionEdit($id)
    {
        $user = User::findOne($id);

        $model = UserForm::create('update');

        $model->id = $user->id;
        $model->login = $user->login;
        $model->name = $user->name;

        if ($model->load(Yii::$app->request->post()) && $model->updateUser()) {
            return $this->redirect(['user/list']);
        } else {
            return $this->render('edit', [
                'model' => $model,
            ]);
        }
    }

    public function actionDelete($id)
    {
        $model = UserForm::create('delete');
        if ($model->load(['id'=>$id], '') && $model->deleteUser()) {
            return $this->redirect(['user/list']);
        } else {
            return $this->redirect(['user/list']);
        }
    }
}
