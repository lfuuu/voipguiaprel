<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\billing_uu\ImsiPartner;
use Yii;
use yii\db\StaleObjectException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class ImsiPartnerController extends JsonController
{
    /**
     * @return ImsiPartner[]
     * @throws ForbiddenHttpException
     */
    public function actionList()
    {
        if (!\Yii::$app->user->can('imsi_partner_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            ImsiPartner::find()
                ->select(['id', 'name'])
                ->orderBy('id')
                ->asArray()
                ->all();
    }
    
    /**
     * @return ImsiPartner[]
     * @throws ForbiddenHttpException
     */
    public function actionRead()
    {
        if (!\Yii::$app->user->can('imsi_partner_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            ImsiPartner::find()
                ->select(['billing_uu.sim_imsi_partner.*', 'ps.name as mvno_region_name'])
                ->leftJoin('public.server ps', 'ps.id = billing_uu.sim_imsi_partner.mvno_region_id')
                ->orderBy('id')
                ->asArray()
                ->all();
    }
    
    /**
     * @return array
     * @throws ForbiddenHttpException
     * @throws HttpException
     */
    public function actionGet()
    {
        if (!\Yii::$app->user->can('imsi_partner_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item =
            ImsiPartner::find()
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
        
        if ($item === null) {
            throw new HttpException(404, 'IMSI партнер не найден');
        }

        return $item;
    }

    /**
     * @throws FormValidationException
     * @throws HttpException
     * @throws \yii\db\Exception
     */
    public function actionSave()
    {
        if (!\Yii::$app->user->can('imsi_partner_edit') && !\Yii::$app->user->can('imsi_partner_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $result = [];
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('imsi_partner_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
    
            $item = $this->getImsiPartnerOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('imsi_partner_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            $item = ImsiPartner::create();
            $result['log'] = ['data_before' => []];
        }
    
        $item->load($this->request, '');

        $transaction = ImsiPartner::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive()) {
                $transaction->rollBack();
            }
        }
    
        $result['log']['data_after'] = $this->getDataForLog($item);
    
        return $result;
    }

    /**
     * @throws StaleObjectException
     * @throws HttpException
     * @throws \Exception
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('imsi_partner_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getImsiPartnerOr404($this->request['id']);
        $item->delete();
    }
}
