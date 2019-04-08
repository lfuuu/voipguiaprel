<?php

namespace app\controllers\json;

use app\models\billing_uu\Pricelist;
use app\models\billing_uu\PricelistFilterB;
use app\models\billing_uu\PricelistPrefixPrice;
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
    
        $searchArray = $this->request['search_array'];
        $limit = $this->request['limit'];
        $offset = $this->request['offset'];
        
        $query = Pricelist::find()
            ->select([
                'billing_uu.pricelist.*',
                'g.name as group_name',
                'is_in_use' => new Expression('case when atl.id is not null then true else false end')
            ])
            ->distinct()
            ->leftJoin('billing_uu.pricelist_group g', 'g.id = billing_uu.pricelist.pricelist_group_id')
            ->leftJoin('billing_uu.package_pricelist pp', 'pp.nnp_pricelist_id = billing_uu.pricelist.id')
            ->leftJoin('billing_uu.account_tariff_light atl', 'atl.tariff_id = pp.tariff_id')
            ->orderBy('name')
            ->limit($limit)
            ->offset($offset)
            ->asArray();
        
        $countQuery = Pricelist::find()
            ->select(['id'])
            ->distinct();
        
        if (isset($searchArray['group_id']) && $searchArray['group_id'] && $searchArray['group_id'] != 'all') {
            $query->where(['billing_uu.pricelist.pricelist_group_id' => $searchArray['group_id']]);
            $countQuery->where(['pricelist_group_id' => $searchArray['group_id']]);
        }
        
        if (isset($searchArray['service_type_id']) && $searchArray['service_type_id']) {
            $query->andWhere(['billing_uu.pricelist.service_type_id' => $searchArray['service_type_id']]);
            $countQuery->andWhere(['service_type_id' => $searchArray['service_type_id']]);
        }
    
        if (isset($searchArray['query']) && $searchArray['query']) {
            $query->andWhere('billing_uu.pricelist.name like :name');
            $query->addParams([':name' => '%' . $searchArray['query'] . '%']);
            $countQuery->andWhere('name like :name');
            $countQuery->addParams([':name' => '%' . $searchArray['query'] . '%']);
        }
        
        $data = $query->all();
    
        $count = $countQuery->count();
        
        return [
            'totalCount' => $count,
            'data' => $data
        ];
    }
    
    public function actionGetWithDependents()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
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
        if (!\Yii::$app->user->can('pricelist_list')) {
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
        if (!\Yii::$app->user->can('pricelist_list')) {
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
    
    public function actionSaveAndUpdate()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getPricelistOr404($this->request['id']);
        $item->load($this->request, '');
        
        $transaction = Pricelist::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }
            
            $filterBArray = PricelistFilterB::find()
                ->alias('fb')
                ->select('fb.*')
                ->innerJoin('billing_uu.pricelist_filter_a as fa', 'fa.id = fb.pricelist_filter_a_id')
                ->innerJoin('billing_uu.pricelist_location as pl', 'pl.id = fa.pricelist_location_id')
                ->with('prefixPriceBasic')
                ->where('pl.pricelist_id = :pricelist_id')
                ->addParams([':pricelist_id' => $this->request['id']])
                ->all();
            
            foreach ($filterBArray as $filterB) {
                $filterB->tarification_free_seconds = $item->default_tarification_free_seconds;
                $filterB->tarification_interval_seconds = $item->default_tarification_interval_seconds;
                $filterB->tarification_min_paid_seconds = $item->default_tarification_min_paid_seconds;
                $filterB->tarification_type = $item->default_tarification_type;
                
                $prefixesToSave = [];
                $prefixesToSaveFlat = [];
                
                foreach ($filterB->prefixPriceBasic as $prefixPrice) {
                    if (!isset($prefixesToSave[$prefixPrice->prefix_b])) {
                        $prefixesToSave[$prefixPrice->prefix_b] = ['date_from' => $prefixPrice->date_from, 'id' => $prefixPrice->id];
                    } else {
                        $dateFromCompare = date_create_from_format('Y-m-d', $prefixesToSave[$prefixPrice->prefix_b]['date_from']);
                        $dateFromCurrent = date_create_from_format('Y-m-d', $prefixPrice->date_from);
                        if ($dateFromCurrent > $dateFromCompare) {
                            $prefixesToSave[$prefixPrice->prefix_b] = ['date_from' => $prefixPrice->date_from, 'id' => $prefixPrice->id];
                        }
                    }
                }
                
                foreach ($prefixesToSave as $prefix) {
                    $prefixesToSaveFlat[] = $prefix['id'];
                }
                
                foreach ($filterB->prefixPriceBasic as $prefixPrice) {
                    if (in_array($prefixPrice->id, $prefixesToSaveFlat)) {
                        $prefixPrice->date_from = $item->date_start;
    
                        if (!$prefixPrice->save()) {
                            throw new FormValidationException($item);
                        }
                    } else {
                        $prefixPrice->delete();
                    }
                }
    
                if (!$filterB->save()) {
                    throw new FormValidationException($item);
                }
            }
            
            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }
    
    public function actionToggleActive()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getPricelistOr404($this->request['id']);
        
        if ($item->isInCommercialUse() && $item->is_active) {
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
    
        $result = (new Query())->select(['id' => new Expression('billing_uu.clone_pricelist(:old_pricelist_id)')])->addParams([':old_pricelist_id' => $this->request['id']])->one();
        
        return $result;
    }
    
    public function actionCopyAndMultiply()
    {
        if (!\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $result = (new Query())
            ->select(['id' => new Expression('billing_uu.clone_pricelist(:old_pricelist_id, :multiplier)')])
            ->addParams(
                [
                    ':old_pricelist_id' => $this->request['id'],
                    ':multiplier' => $this->request['multiplier']
                ]
            )
            ->one();
        
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
