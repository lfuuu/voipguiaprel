<?php

namespace app\controllers\json;

use app\models\auth\Hub;
use app\classes\JsonController;
use yii\web\ForbiddenHttpException;

class HubController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('hub_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $result =
            Hub::find()
                ->select(['id', 'name'])
                ->orderBy('id')
                ->asArray()
                ->all();
        
        $result = array('none' => array('id' => 'none', 'name' => 'Не выбрано')) + $result;
        
        return $result;
    }
}
