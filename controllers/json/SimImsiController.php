<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\models\billing_uu\SimImsiPartner;
use app\models\billing_uu\SimImsiProfile;

class SimImsiController extends JsonController
{

    /**
     * @return array
     */
    public function actionPartner()
    {
        return SimImsiPartner::find()
            ->select(['id', 'name'])
            ->orderBy('id')
            ->asArray()
            ->all();
    }

    /**
     * @return array
     */
    public function actionProfile()
    {
        return SimImsiProfile::find()
            ->select(['id', 'name'])
            ->orderBy('id')
            ->asArray()
            ->all();
    }
}
