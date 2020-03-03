<?php

namespace app\controllers\json;

use app\models\billing_uu\Major;
use app\models\billing_uu\PricelistFilterB;
use app\models\billing_uu\PricelistPrefixPrice;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use DateTime;
use yii\db\Expression;
use yii\db\Query;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class PricelistFilterBController extends JsonController
{
    public function actionGet()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            PricelistFilterB::find()
                ->alias('b')
                ->select(
                    ['b.*', 'date_trunc(\'second\', time_start) as time_start',
                    'date_trunc(\'second\', time_end) as time_end',
                    'filter_country' => 'm.country_code']
                )
                ->leftJoin(['m' => Major::tableName()], 'm.id = b.nnp_filter')
                ->where(['b.id' => $this->request['id']])
                ->asArray()
                ->one();
    }
    
    public function actionSave()
    {
        if (!\Yii::$app->user->can('pricelist_edit') && !\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $result = [];
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('pricelist_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getPricelistFilterBOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('pricelist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = PricelistFilterB::create();
            $result['log'] = ['data_before' => []];
        }
    
        if (isset($this->request['prefixes'])) {
            if (preg_match("/[^\d,.\-\s]/", $this->request['prefixes'])) {
                return ['error' => 'Некорректный формат префиксов!', 'field' => 'prefixes'];
            }
            
            if (isset($this->request['prefixes_date_start'])) {
                $dt = DateTime::createFromFormat("Y-m-d", $this->request['prefixes_date_start']);
                if ($dt !== false && !array_sum($dt::getLastErrors())) {
                    $dateStart = $this->request['prefixes_date_start'];
                } else {
                    return ['error' => 'Некорректный формат даты!', 'field' => 'prefixes_date_start'];
                }
            } else {
                $dateStart = date('Y-m-d');
            }
            
            if (isset($this->request['prefixes_date_end'])) {
                $dt = DateTime::createFromFormat("Y-m-d", $this->request['prefixes_date_end']);
                if ($dt !== false && !array_sum($dt::getLastErrors())) {
                    $dateEnd = $this->request['prefixes_date_end'];
                } else {
                    return ['error' => 'Некорректный формат даты!', 'field' => 'prefixes_date_end'];
                }
            } else {
                $dateEnd = '3000-01-01';
            }
        }
        
        $item->load($this->request, '');
        
        $transaction = PricelistFilterB::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }
            
            if (isset($this->request['prefixes'])) {
                $prefixesToSave = [];
                $prefixesArray = explode("\n", $this->request['prefixes']);
                
                foreach ($prefixesArray as $prefixItem) {
                    list($prefixBString, $prefixPrice) = preg_split("/[\t]/", $prefixItem);
                    
                    $prefixBArray = explode(',', str_replace(['-'], ',', $prefixBString));
                    
                    foreach ($prefixBArray as $prefixB) {
                        $prefixesToSave[] = [
                            'pricelist_filter_b_id' => $item->id,
                            'prefix_b' => trim($prefixB),
                            'b_number_price' => str_replace(',', '.', $prefixPrice),
                            'date_from' => $dateStart,
                            'date_to' => $dateEnd
                        ];
                    }
                }
                
                foreach ($prefixesToSave as $prefixToSave) {
                    $prefixCreatedItem = PricelistPrefixPrice::create($prefixToSave);
                    if (!$prefixCreatedItem->save()) {
                        throw new FormValidationException($item);
                    }
                }
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
        if (!\Yii::$app->user->can('pricelist_edit') && !\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $result = [];

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('pricelist_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }

            $item = $this->getPricelistFilterBOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('pricelist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }

            $item = PricelistFilterB::create();
            $result['log'] = ['data_before' => []];
        }

        if (isset($this->request['prefixes'])) {
            if (preg_match("/[^\d,.\-\s]/", $this->request['prefixes'])) {
                return ['error' => 'Некорректный формат префиксов!'];
            }
        }

        $item->load($this->request, '');

        $transaction = PricelistFilterB::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }

            if (isset($this->request['prefixes'])) {
                $prefixesToSave = [];
                $prefixesArray = explode("\n", $this->request['prefixes']);

                foreach ($prefixesArray as $prefixItem) {
                    list($prefixBString, $prefixPrice) = preg_split("/[\t]/", $prefixItem);

                    $prefixBArray = explode(',', str_replace(['-'], ',', $prefixBString));

                    foreach ($prefixBArray as $prefixB) {
                        $prefixesToSave[] = [
                            'pricelist_filter_b_id' => $item->id,
                            'prefix_b' => trim($prefixB),
                            'b_number_price' => str_replace(',', '.', $prefixPrice),
                            'date_from' => date('Y-m-d'),
                            'date_to' => '3000-01-01'
                        ];
                    }
                }

                foreach ($prefixesToSave as $prefixToSave) {
                    $prefixCreatedItem = PricelistPrefixPrice::create($prefixToSave);
                    if (!$prefixCreatedItem->save()) {
                        throw new FormValidationException($item);
                    }
                }
            }

            $id = $item->id;

            (new Query())->select(new Expression('billing_uu.copy_b_nnp_filter(:filter_b_id)'))
                ->addParams([
                    ':filter_b_id' => $id
                ])->one();

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }

        if ($id) {
            $item = $this->getPricelistFilterBOr404($id);
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
        if (!\Yii::$app->user->can('pricelist_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getPricelistFilterBOr404($this->request['id']);
        $item->delete();
    }
}
