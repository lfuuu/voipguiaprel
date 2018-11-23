<?php

namespace app\controllers\json;

use app\models\billing_uu\Major;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\db\IntegrityException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class MajorController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('major_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        return
            Major::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }
    
    public function actionRead()
    {
        if (!\Yii::$app->user->can('major_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $countryCode = $this->request['country_code'];
        
        $query = Major::find()
            ->alias('m')
            ->select(['m.*', 'country_name' => 'nc.name_rus'])
            ->where(['m.country_code' => $countryCode])
            ->innerJoin('nnp.country nc', 'nc.code = m.country_code')
            ->orderBy('order')
            ->asArray();
        
        return $query->all();
    }
    
    public function actionGet()
    {
        if (!\Yii::$app->user->can('major_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            Major::find()
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
    }
    
    public function actionSave()
    {
        if (!\Yii::$app->user->can('major_edit') && !\Yii::$app->user->can('major_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('major_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getMajorOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('major_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = Major::create();
        }
        
        $item->load($this->request, '');
    
        $item->setNnpFilters($this->request);
        
        $transaction = Major::getDb()->beginTransaction();
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
    
    public function actionMove()
    {
        if (!\Yii::$app->user->can('major_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $direction = $this->request['direction'];
        $item = $this->getMajorOr404($this->request['id']);
        $order = $direction == 'up' ? $item->order - 1 : $item->order + 1;
        
        $neigbour = Major::find()
            ->where(['country_code' => $item->country_code, 'order' => $order])
            ->one();
        
        $neigbour->order = $item->order;
        $item->order = $order;
    
        $transaction = Major::getDb()->beginTransaction();
        try {
            if (!$item->save() || !$neigbour->save()) {
                throw new FormValidationException($item);
            }
        
            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }
    
    /**
     * @throws HttpException
     * @throws \Exception
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('major_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getMajorOr404($this->request['id']);
    
        $neigbours = Major::find()
            ->where(['country_code' => $item->country_code])
            ->andWhere('"order" > :order')
            ->addParams([':order' => $item->order])
            ->all();
        
        try {
            foreach ($neigbours as $neigbour) {
                $neigbour->order = $neigbour->order - 1;
                $neigbour->save();
            }
            
            $item->delete();
        } catch (IntegrityException $e) {
            return ['errors' => [['code' => $e->getCode(), 'message' => $e->getMessage()]]];
        }
    }
}
