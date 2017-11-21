<?php
namespace app\controllers;

use Yii;
use app\classes\BaseController;
use app\forms\AclForm;
use app\models\Acl;
use yii\web\ForbiddenHttpException;

class AclController extends BaseController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('acl_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return $this->render('list', [
            'acl_list' => Acl::find()->orderBy('name')->all(),
        ]);
    }
}
