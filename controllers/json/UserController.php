<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\models\User;
use yii\web\ForbiddenHttpException;

class UserController extends JsonController
{
    protected $doNotLog = true;

    public function actionRead()
    {
        if (!\Yii::$app->user->can('user_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        return
            User::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }
}
