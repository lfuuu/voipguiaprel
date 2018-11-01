<?php

namespace app\controllers\json;

use app\models\billing_uu\Pricelist;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\base\Exception;
use yii\db\Expression;
use yii\db\IntegrityException;
use yii\db\Query;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class PricelistController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        return
            Pricelist::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }
    
    public function actionRead()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $groupId = $this->request['group_id'];
        $limit = $this->request['limit'];
        $offset = $this->request['offset'];
        
        $data = Pricelist::find()
                ->select([
                    'billing_uu.pricelist.*',
                    'g.name as group_name',
                    'is_in_use' => new Expression('case when atl.id is not null then true else false end')
                ])
                ->leftJoin('billing_uu.pricelist_group g', 'g.id = billing_uu.pricelist.pricelist_group_id')
                ->leftJoin('billing_uu.package_pricelist pp', 'pp.nnp_pricelist_id = billing_uu.pricelist.id')
                ->leftJoin('billing_uu.account_tariff_light atl', 'atl.id = pp.tariff_id')
                ->where(['billing_uu.pricelist.pricelist_group_id' => $groupId])
                ->orderBy('name')
                ->limit($limit)
                ->offset($offset)
                ->asArray()
                ->all();
    
        $count = Pricelist::find()
            ->select(['id'])
            ->where(['pricelist_group_id' => $groupId])
            ->count();
    
        return [
            'totalCount' => $count,
            'data' => $data
        ];
    }
    
    public function actionGetWithDependents()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            Pricelist::find()
                ->with('location.filterA.filterB.prefixPrice')
                ->with('location.filterA.filterB.prefixPriceCount')
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
    }
    
    public function actionGetWithDependentsNoLimit()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            Pricelist::find()
                ->with('location.filterA.filterB.prefixPriceNoLimit')
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
    }
    
    public function actionGet()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            Pricelist::find()
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
    }
    
    public function actionSave()
    {
        if (!\Yii::$app->user->can('pricelist_edit') && !\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('pricelist_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getPricelistOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('pricelist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = Pricelist::create();
        }
        
        $item->load($this->request, '');
        
        $transaction = Pricelist::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }
            
            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
        
        if (isset($this->request['old_pricelist_id'])) {
            $item->importFromOldVersion($this->request['old_pricelist_id']);
        }
    }
    
    public function actionToggleActive()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getPricelistOr404($this->request['id']);
        
        if ($item->isInCommercialUse()) {
            throw new Exception('In commercial use!');
        } else {
            $item->is_active = !$item->is_active;
            $item->save();
        }
        
    }
    
    public function actionInherit()
    {
        if (!\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $name = $this->request['name'];
        
        if (!empty($name)) {
            $result = (new Query())->select(['id' => new Expression('billing_uu.clone_pricelist(:old_pricelist_id, true, :name)')])
                ->addParams([
                    ':old_pricelist_id' => $this->request['id'],
                    ':name' => $name
                ])->one();
        } else {
            $result = (new Query())->select(['id' => new Expression('billing_uu.clone_pricelist(:old_pricelist_id, true)')])
                ->addParams([
                    ':old_pricelist_id' => $this->request['id']
                ])->one();
        }
        
        return $result;
    }
    
    public function actionCopy()
    {
        if (!\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $result = (new Query())->select(['id' => new Expression('billing_uu.clone_pricelist(:old_pricelist_id, false)')])->addParams([':old_pricelist_id' => $this->request['id']])->one();
        
        return $result;
    }
    
    /**
     * @throws StaleObjectException
     * @throws HttpException
     * @throws \Exception
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('pricelist_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getPricelistOr404($this->request['id']);
        
        try {
            $item->delete();
        } catch (IntegrityException $e) {
            return ['errors' => [['code' => $e->getCode(), 'message' => $e->getMessage()]]];
        }
    }
}
