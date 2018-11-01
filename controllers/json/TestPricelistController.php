<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\auth\TestPricelist;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use Yii;

class TestPricelistController extends JsonController
{
    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws HttpException
     */
    public function actionList()
    {
        if (!\Yii::$app->user->can('test_pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
                
        return
            TestPricelist::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws HttpException
     */
    public function actionRead()
    {
        if (!\Yii::$app->user->can('test_pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $testGroupId = $this->request['test_group_id'];
        $limit = $this->request['limit'];
        $offset = $this->request['offset'];
        
        $data = TestPricelist::find()
                ->select(['auth.test_pricelist.*', 'mcc.country as mcc_name', 'mnc.network as mnc_name', 'p.name as pricelist_name',
                    new Expression('case 
                        when auth.test_pricelist.location_id = 1 then \'Домашний регион\' 
                        when auth.test_pricelist.location_id = 2 then \'Гостевой регион\' 
                        when auth.test_pricelist.location_id = 3 then \'Международный регион\' 
                        end as location_name')])
                ->leftJoin('nnp.mcc as mcc', 'mcc.mcc = auth.test_pricelist.mcc')
                ->leftJoin('nnp.mnc as mnc', 'mnc.mnc = auth.test_pricelist.mnc')
                ->leftJoin('billing_uu.pricelist as p', 'p.id = auth.test_pricelist.pricelist_id')
                ->where(['auth.test_pricelist.test_pricelist_group_id' => $testGroupId])
                ->orderBy('name')
                ->limit($limit)
                ->offset($offset)
                ->asArray()
                ->all();
    
        $count = TestPricelist::find()
            ->select(['id'])
            ->where(['test_pricelist_group_id' => $testGroupId])
            ->count();
    
        return [
            'totalCount' => $count,
            'data' => $data
        ];
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionGet()
    {
        if (!\Yii::$app->user->can('test_pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = TestPricelist::find()
            ->where(['id' => $this->request['id']])
            ->asArray()
            ->one();

        if ($item === null) {
            throw new HttpException(404, 'TestPricelist не найден');
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
        if (!\Yii::$app->user->can('test_pricelist_edit') && !\Yii::$app->user->can('test_pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('test_pricelist_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getTestPricelistOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('test_pricelist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = TestPricelist::create();
        }

        $item->load($this->request, '');

        $transaction = TestPricelist::getDb()->beginTransaction();
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

    /**
     * @inheritdoc
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('test_pricelist_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = TestPricelist::findOne($this->request['id']);
        $item->delete();
    }
    
    /**
     * @return array
     * @throws HttpException
     */
    public function actionResult()
    {
        if (!\Yii::$app->user->can('test_pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getTestPricelistOr404($this->request['id']);
        
        $server = $this->getServerOr404($this->request['server_id']);
        
        $apiUrl = $server->apiUrl;
    
        $apiParams = [
            'cmd' => 'priceV2Calc',
            'num_a' => $item->a_number,
            'num_b' => $item->b_number,
            'mcc' => $item->mcc,
            'mnc' => $item->mnc,
            'location_id' => $item->location_id,
            'pricelist_id' => $item->pricelist_id,
            'orig' => $item->is_orig ? 'true' : 'false',
            'test_mode' => true
        ];
        
        $request = $apiUrl . 'test/nnpcalc?' . http_build_query($apiParams);
        
        $response = file_get_contents($request);
        
        return [
            'steps' => [json_decode($response, true)],
            'number_range_a' => $this->getNumberRangeByNum($item->a_number, $apiUrl),
            'number_range_b' => $this->getNumberRangeByNum($item->b_number, $apiUrl),
            'destination_a' => $this->getDestinationByNum($item->a_number, $apiUrl),
            'destination_b' => $this->getDestinationByNum($item->b_number, $apiUrl),
            'a_number' => $item->a_number,
            'b_number' => $item->b_number,
            'id' => $item->id,
            'name' => $item->name
        ];
    }
    
    /**
     * @return array
     * @throws HttpException
     */
    public function actionNumberResult()
    {
        if (!\Yii::$app->user->can('test_pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $number = $this->request['number'];
        
        $server = $this->getServerOr404($this->request['server_id']);
        
        $apiUrl = $server->apiUrl;
        
        return [
            'number' => $number,
            'number_range' => $this->getNumberRangeByNum($number, $apiUrl),
            'destination' => $this->getDestinationByNum($number, $apiUrl),
        ];
    }
    
    /**
     * @return array
     * @throws HttpException
     */
    public function actionGetNumberRangeByNum()
    {
        if (!\Yii::$app->user->can('test_pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $number = $this->request['number'];
    
        $server = $this->getServerOr404($this->request['server_id']);
    
        $apiUrl = $server->apiUrl;
        
        return $this->getNumberRangeByNum($number, $apiUrl);
    }
    
    /**
     * @return array
     * @throws HttpException
     */
    public function actionGetDestinationByNum()
    {
        if (!\Yii::$app->user->can('test_pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $number = $this->request['number'];
        
        $server = $this->getServerOr404($this->request['server_id']);
        
        $apiUrl = $server->apiUrl;
        
        return $this->getDestinationByNum($number, $apiUrl);
    }
    
    private function getNumberRangeByNum($number, $apiUrl)
    {
        $fields = [
            'nnp_city_id' => 'app\models\nnp\City',
            'ndc_type_id' => 'app\models\nnp\NdcType',
            'nnp_operator_id' => 'app\models\nnp\Operator',
            'nnp_region_id' => 'app\models\nnp\Region',
            'ported_operator_id' => 'app\models\nnp\Operator',
        ];
        
        return $this->getCmdByNum('getNumberRangeByNum', $number, $apiUrl, $fields);
    }
    
    private function getDestinationByNum($number, $apiUrl)
    {
        return $this->getCmdByNum('getDestinationByNum', $number, $apiUrl);
    }
    
    private function getCmdByNum($cmd, $number, $apiUrl, $fields = [])
    {
        $apiParams = [
            'cmd' => $cmd,
            'num' => $number
        ];
    
        $request = $apiUrl . 'test/nnpcalc?' . http_build_query($apiParams);

        $response = file_get_contents($request);
        $result = json_decode($response, true);
        
        if (!empty($fields) && !empty($result)) {
            foreach ($result as $key => $value) {
                if (array_key_exists($key, $fields)) {
                    $newItem = $fields[$key]::findOne(['id' => $value]);
                    if ($newItem) {
                        $result[$key] = $newItem->name;
                    }
                }
            }
        }
        
        return $result;
    }
}
