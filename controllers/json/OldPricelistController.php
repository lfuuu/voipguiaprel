<?php

namespace app\controllers\json;

use app\models\voip\Pricelist;
use app\classes\JsonController;
use yii\web\ForbiddenHttpException;

class OldPricelistController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        return
            Pricelist::find()
                ->select(['id', 'name'])
                ->orderBy('id')
                ->asArray()
                ->all();
    }
}
