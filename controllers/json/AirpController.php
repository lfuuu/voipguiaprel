<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\Airp;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class AirpController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('airp_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $server = $this->getServerOr404($this->request['server_id']);

        return
            Airp::find()
                ->select(['id', 'name'])
                ->where(['server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead()
    {
        if (!\Yii::$app->user->can('airp_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);

        return
            Airp::find()
                ->select(['id', 'name', 'object_comment'])
                ->where(['server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('airp_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = Airp::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'AIRP не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('airp_edit') && !\Yii::$app->user->can('airp_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('airp_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getAirpOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('airp_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = Airp::create($server);
        }

        $item->load($this->request, '');

        $transaction = Airp::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }

    public function actionDelete()
    {
        if (!\Yii::$app->user->can('airp_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = Airp::findOne($this->request['id']);
        $item->delete();
    }
}
