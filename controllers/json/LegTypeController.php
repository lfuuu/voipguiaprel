<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\models\billing\LegType;
use yii\web\ForbiddenHttpException;

class LegTypeController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('trunk_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        return
            LegType::find()
                ->select(['id', 'name' => 'note'])
                ->orderBy('note')
                ->asArray()
                ->all();
    }
}
