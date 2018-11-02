<?php

namespace app\controllers\json;

use app\models\billing_uu\PricelistPrefixPrice;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\base\Exception;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class PricelistPrefixPriceController extends JsonController
{
    public function actionGet()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            PricelistPrefixPrice::find()
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
    }
    
    public function actionRead()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $pageNumber = $this->request['page_number'];
        $offset = ($pageNumber - 1) * PricelistPrefixPrice::PAGE_LIMIT;
        $limit = PricelistPrefixPrice::PAGE_LIMIT;
        
        return
            PricelistPrefixPrice::find()
                ->where(['pricelist_filter_b_id' => $this->request['pricelist_filter_b_id']])
                ->orderBy('prefix_b')
                ->offset($offset)
                ->limit($limit)
                ->asArray()
                ->all();
    }
    
    public function actionSave()
    {
        if (!\Yii::$app->user->can('pricelist_edit') && !\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $prefixB = $this->request['prefix_b'];
        $filterBId = $this->request['pricelist_filter_b_id'];
        $id = isset($this->request['id']) ? $this->request['id'] : null;

        $result = PricelistPrefixPrice::checkIfPrefixExists($prefixB, $filterBId, $id);

        if ($result) {
            return ['error' => 'В этом фильтре B уже есть такой префикс!', 'field' => 'prefix_b'];
        }
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('pricelist_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getPricelistPrefixPriceOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('pricelist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = PricelistPrefixPrice::create();
        }
        
        $item->load($this->request, '');
        
        $transaction = PricelistPrefixPrice::getDb()->beginTransaction();
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
     * @throws StaleObjectException
     * @throws HttpException
     * @throws \Exception
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('pricelist_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getPricelistPrefixPriceOr404($this->request['id']);
        $item->delete();
    }
}
