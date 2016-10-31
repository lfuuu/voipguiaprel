<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\ReleaseReason;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class ReleaseReasonController extends JsonController
{
    public function actionList() {
        $server = $this->getServerOr404($this->request['server_id']);
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;
	
        return
            ReleaseReason::find()
                ->select(['id', 'name'])
                ->where("( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $server = $this->getServerOr404($this->request['server_id']);
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;

        return
            ReleaseReason::find()
                ->select(['id', 'name','server_id','sw_shared'])
                ->where("( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item = ReleaseReason::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'Release reason не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            $releaseReason = $this->getReleaseReasonOr404($this->request['id']);
        } else {
            $releaseReason = ReleaseReason::create($server);
        }

        $releaseReason->load($this->request, '');

        $transaction = ReleaseReason::getDb()->beginTransaction();
        try {
            if (!$releaseReason->save()) {
                throw new FormValidationException($releaseReason);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }

    public function actionDelete()
    {
        $item = ReleaseReason::findOne($this->request['id']);
        $item->delete();
    }
}
