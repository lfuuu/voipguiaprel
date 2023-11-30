<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\UvrGroup;

class UvrGroupController extends JsonController
{
    public function actionList()
    {
        return
            UvrGroup::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }
}
