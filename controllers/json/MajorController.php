<?php

namespace app\controllers\json;

use app\models\billing_uu\Major;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\billing_uu\Pricelist;
use app\models\billing_uu\PricelistFilterA;
use app\models\billing_uu\PricelistFilterB;
use app\models\billing_uu\PricelistGroup;
use app\models\billing_uu\PricelistLocation;
use yii\db\Expression;
use yii\db\IntegrityException;
use yii\db\Query;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class MajorController extends JsonController
{
    const API_URL = 'http://reg10.mcntelecom.ru:8032/';
    
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
        $groupId = isset($this->request['group_id']) ? $this->request['group_id'] : 'undefined';
        
        if ($countryCode == 'undefined' && $groupId == 'undefined') {
            return [];
        } elseif ($countryCode == 'undefined' && $groupId != 'undefined') {
            $where = ['m.major_group_id' => $groupId];
        } elseif ($countryCode != 'undefined' && $groupId == 'undefined') {
            $where = ['m.country_code' => $countryCode];
        } else {
            $where = ['m.country_code' => $countryCode, 'm.major_group_id' => $groupId];
        }
        
        $query = Major::find()
            ->alias('m')
            ->select(['m.*', 'country_name' => 'nc.name_rus', 'group_name' => 'mg.name'])
            ->where($where)
            ->innerJoin('nnp.country nc', 'nc.code = m.country_code')
            ->leftJoin('billing_uu.major_group mg', 'mg.id = m.major_group_id')
            ->orderBy('order')
            ->asArray();
        
        return $query->all();
    }
    
    public function actionGet()
    {
        if (!\Yii::$app->user->can('major_list')) {
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
    
        $result = [];
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('major_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getMajorOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('major_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = Major::create();
            $result['log'] = ['data_before' => []];
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
    
        $result['log']['data_after'] = $this->getDataForLog($item);
    
        return $result;
    }
    
    public function actionSaveAndUpdate()
    {
        if (!\Yii::$app->user->can('major_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getMajorOr404($this->request['id']);
        
        $item->load($this->request, '');
        
        $item->setNnpFilters($this->request);
        
        $transaction = Major::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }
    
            (new Query())->select(new Expression('billing_uu.copy_nnp_to_filters(:major_id)'))
                ->addParams([
                    ':major_id' => $this->request['id']
                ])->one();
            
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
    
    /**
     * @return array
     * @throws HttpException
     */
    public function actionTest()
    {
        if (!\Yii::$app->user->can('major_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getMajorOr404($this->request['id']);
    
        $apiUrl = self::API_URL;
        
        $filter = json_decode($item->nnp_filter_json, true);
        
        $apiParams = [
            'cmd' => 'getPrefixByFilter',
            'complement' => 'true',
            'factor' => $this->request['factor'],
        ];
        
        if (isset($filter['country_code'])) {
            $apiParams['country_code'] = implode(',', $filter['country_code']);
        }
        
        if (isset($filter['operator_id'])) {
            $apiParams['operator_id'] = implode(',', $filter['operator_id']);
        }
        
        if (isset($filter['region_id'])) {
            $apiParams['region_id'] = implode(',', $filter['region_id']);
        }
        
        if (isset($filter['city_id'])) {
            $apiParams['city_id'] = implode(',', $filter['city_id']);
        }

        if (isset($filter['ndc'])) {
            $apiParams['ndc'] = implode(',', $filter['ndc']);
        }
        
        if (isset($filter['ndc_type_id'])) {
            $apiParams['ndc_type_id'] = implode(',', $filter['ndc_type_id']);
        }
        
        $exclude_country = isset($filter['exclude_country']) ? ($filter['exclude_country'] ? true : false) : false;
        $exclude_oper = isset($filter['exclude_operators']) ? ($filter['exclude_operators'] ? true : false) : false;
        $exclude_region = isset($filter['exclude_region']) ? ($filter['exclude_region'] ? true : false) : false;
        $exclude_city = isset($filter['exclude_city']) ? ($filter['exclude_city'] ? true : false) : false;
        $exclude_ndc_type = isset($filter['exclude_ndc_type']) ? ($filter['exclude_ndc_type'] ? true : false) : false;
        $exclude_ndc = isset($filter['exclude_ndc']) ? ($filter['exclude_ndc'] ? true : false) : false;

        if ($exclude_country) {
            $apiParams['exclude_country'] = 'true';
        }
    
        if ($exclude_oper) {
            $apiParams['exclude_oper'] = 'true';
        }
    
        if ($exclude_region) {
            $apiParams['exclude_region'] = 'true';
        }
    
        if ($exclude_city) {
            $apiParams['exclude_city'] = 'true';
        }

        if ($exclude_ndc_type) {
            $apiParams['exclude_ndc_type'] = 'true';
        }
    
        if ($exclude_ndc) {
            $apiParams['exclude_ndc'] = 'true';
        }
        
        $request = $apiUrl . 'test/nnpcalc?' . http_build_query($apiParams);
        
        $response = json_decode(file_get_contents($request), true);
        
        return [
            'item' => $response,
            'url' => $request
        ];
    }
    
    /**
     * @return array
     * @throws HttpException
     */
    public function actionFindUsagesInPricelists()
    {
        if (!\Yii::$app->user->can('major_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return Pricelist::find()
            ->select([
                'p.*', 'group_name' => 'pg.name'
            ])
            ->distinct()
            ->alias('p')
            ->innerJoin(['pg' => PricelistGroup::tableName()], 'pg.id = p.pricelist_group_id')
            ->innerJoin(['pl' => PricelistLocation::tableName()], 'pl.pricelist_id = p.id')
            ->innerJoin(['a' => PricelistFilterA::tableName()], 'pl.id = a.pricelist_location_id')
            ->innerJoin(['b' => PricelistFilterB::tableName()], 'a.id = b.pricelist_filter_a_id')
            ->where(['a.nnp_filter' => $this->request['id']])
            ->orWhere(['b.nnp_filter' => $this->request['id']])
            ->asArray()
            ->orderBy('p.id')
            ->all();
    }
}
