<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\Number;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class NumberController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('number_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        try {
            $server = $this->getServerOr404($this->request['server_id']);
            $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;
            $where = "( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id;
        } catch (HttpException $e) {
            $server = $this->getServerOcsOr404($this->request['server_id']);
            $where = "server_id = ".$server->id." or sw_share_with_camel";
        }

        return
            Number::find()
                ->select(['id', 'name', 'type_id'])
                ->where($where)
                ->andWhere(['type_id' => $this->request['type_id']])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionListByType()
    {
        if (!\Yii::$app->user->can('number_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        return
            Number::find()
                ->select(['id', 'name', 'type_id'])
                ->where(['type_id' => $this->request['type_id']])
                ->orderBy('name')
                ->asArray()
                ->all();
    }
    
    public function actionRead()
    {
        if (!\Yii::$app->user->can('number_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;

        return
            Number::find()
                ->select(['id', 'name', 'type_id', 'prefixlist_ids', 'show_in_stat', 'server_id', 'sw_shared', 'object_comment'])
                ->where("( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('number_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = Number::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'Номер не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('number_edit') && !\Yii::$app->user->can('number_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $result = [];
    
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('number_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $number = $this->getNumberOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($number)];
        } else {
            if (!\Yii::$app->user->can('number_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $number = Number::create($server);
            $result['log'] = ['data_before' => []];
        }

        $number->load($this->request, '');
        $number->setPrefixlists($this->request['prefixlist_ids']);

        $transaction = Number::getDb()->beginTransaction();
        try {
            if (!$number->save()) {
                throw new FormValidationException($number);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    
        $result['log']['data_after'] = $this->getDataForLog($number);
        
        return $result;
    }

    public function actionDelete()
    {
        if (!\Yii::$app->user->can('number_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = Number::findOne($this->request['id']);
        $item->delete();
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionFindUsagesInRouteTables()
    {
        if (!\Yii::$app->user->can('number_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $number = $this->getNumberOr404($this->request['id']);

        return $number->findUsagesInRouteTables();
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionFindUsagesInTrunkPriority()
    {
        if (!\Yii::$app->user->can('number_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $number = $this->getNumberOr404($this->request['id']);

        return $number->findUsagesInTrunkPriority();
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionFindUsagesInTrunkRules()
    {
        if (!\Yii::$app->user->can('number_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $number = $this->getNumberOr404($this->request['id']);

        return $number->findUsagesInTrunkRules();
    }
    
    /**
     * @return array
     * @throws HttpException
     */
    public function actionFindUsagesInStatRules()
    {
        if (!\Yii::$app->user->can('number_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $number = $this->getNumberOr404($this->request['id']);
        
        return $number->findUsagesInStatRules();
    }
}
