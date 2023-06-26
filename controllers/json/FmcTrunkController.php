<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\FmcTrunk;
use yii\web\ForbiddenHttpException;

class FmcTrunkController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('prefixlist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            FmcTrunk::find()
                ->select(['fmc_trunk_id as id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }
}
