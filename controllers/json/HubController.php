<?php

namespace app\controllers\json;

use app\models\auth\Hub;
use app\classes\JsonController;
use yii\web\ForbiddenHttpException;
use Yii;

class HubController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('hub_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $isEu = Yii::$app->params['isEuropean'];
        $result = 
            Hub::find()
                ->select(['id', 'name'])
                ->where(['market_place_id' => $isEu ? Hub::EUROPEAN_HUB : Hub::RUSSIAN_HUB])
                ->orderBy('id')
                ->asArray()
                ->all();
        
        $result = array('none' => array('id' => 'none', 'name' => 'Не выбрано')) + $result;
        
        return $result;
    }
}
