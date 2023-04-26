<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\nnp\Mnc;
use app\exceptions\FormValidationException;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class MncController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('mnc_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        return
            Mnc::find()
                ->select(['id' => 'mnc', 'mnc' => new Expression('LPAD(mnc::text, 3, \'0\')'), 'name' => 'network'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead()
    {
        if (!\Yii::$app->user->can('mnc_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            Mnc::find()
                ->select([new Expression('LPAD(nnp.mnc.mcc::text, 3, \'0\') as mcc'), new Expression('LPAD(nnp.mnc.mnc::text, 3, \'0\') as mnc'), 'nnp.mnc.network', 'mcc.country'])
                ->innerJoin('nnp.mcc mcc', 'mcc.mcc = nnp.mnc.mcc')
                ->orderBy('network')
                ->asArray()
                ->all();
    }
    
    public function actionListByMcc()
    {
        if (!\Yii::$app->user->can('mnc_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $mcc = $this->request['mcc'];
        
        return
            Mnc::find()
                ->select(['id' => 'mnc', 'mnc' => new Expression('LPAD(mnc::text, 3, \'0\')'), 'name' => 'network'])
                ->where(['mcc' => $mcc])
                ->orderBy('network')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('mnc_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = Mnc::find()
            ->where(['mcc' => $this->request['mcc']])
            ->andWhere(new Expression('LPAD(mnc::text, 3, \'0\')') . ' = :mnc')
            ->addParams([':mnc' => $this->request['mnc']])
            ->one();
        
        if ($item === null) {
            throw new HttpException(404, 'MNC не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('mnc_edit') && !\Yii::$app->user->can('mnc_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $result = [];
    
        if (isset($this->request['mnc'])) {
            $item = Mnc::find()
                ->where(['mcc' => $this->request['mcc'], 'mnc' => $this->request['mnc']])
                ->one();

            if (empty($item)) {
                if (!\Yii::$app->user->can('mnc_create')) {
                    throw new ForbiddenHttpException('Access denied');
                }
            
                $item = Mnc::create();
                $result['log'] = ['data_before' => []];
            } else {
                if (!\Yii::$app->user->can('mnc_edit')) {
                    throw new ForbiddenHttpException('Access denied');
                }
                
                $result['log'] = ['data_before' => $this->getDataForLog($item)];
            }
        } else {
            if (!\Yii::$app->user->can('mnc_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
        
            $item = Mnc::create();
            $result['log'] = ['data_before' => []];
        }

        $item->load($this->request, '');

        $transaction = Mnc::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    
        $result['log']['data_after'] = $this->getDataForLog($item);
    
        return $result;
    }

    public function actionDelete()
    {
        if (!\Yii::$app->user->can('mnc_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $item = Mnc::findOne([
            'mnc' => (int)$this->request['id']['mnc'], 
            'mcc' => $this->request['id']['mcc']
        ]);

        $item->delete();
    }
}
