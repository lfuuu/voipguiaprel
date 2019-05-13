<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\OcaBw;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class OcaBwController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('oca_bw_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);
    
        return
            OcaBw::find()
                ->select(['id', 'name'])
                ->where("is_global or server_id = ".$server->id)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead()
    {
        if (!\Yii::$app->user->can('oca_bw_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $server = $this->getServerOr404($this->request['server_id']);
    
        return
            OcaBw::find()
                ->select(['id', 'name', 'object_comment'])
                ->where("is_global or server_id = ".$server->id)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('oca_bw_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = OcaBw::findOne($this->request['id']);
        
        if ($item === null) {
            throw new HttpException(404, 'Список OCA BW не найден');
        }

        $result = $item->toArray();
        
        $prefixlist = json_decode($item['prefixlist'], true);
        
        $result['prefixlist'] = $prefixlist ? $prefixlist : [];
        
        return $result;
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('oca_bw_edit') && !\Yii::$app->user->can('oca_bw_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('oca_bw_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getOcaBwOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('oca_bw_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = OcaBw::create();
        }
        
        $prefixlistArray = $this->request['prefixlist'];
        unset($this->request['prefixlist']);

        $item->load($this->request, '');
        $item->prefixlist = json_encode($prefixlistArray);

        $transaction = OcaBw::getDb()->beginTransaction();
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
        if (!\Yii::$app->user->can('oca_bw_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = OcaBw::findOne($this->request['id']);
        $item->delete();
    }
}
