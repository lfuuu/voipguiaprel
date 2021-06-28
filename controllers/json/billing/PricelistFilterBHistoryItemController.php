<?php

namespace app\controllers\json\billing;

use app\classes\JsonController;
use app\models\billing_uu\PricelistFilterB;
use app\models\billing_uu\PricelistFilterBHistoryItem;
use app\models\billing_uu\PricelistFilterBHistory;
use InvalidArgumentException;

class PricelistFilterBHistoryItemController extends JsonController
{
    protected $modelName = 'app\models\billing_uu\PricelistFilterBHistoryItem';
    protected $idParamName = 'id';
    protected $nameParamName = 'description';
    protected $listPermission = 'pricelist_filter_b_history_list';
    protected $readWhere = ['pricelist_filter_b_history_id'];

    public function actionRead()
    {
        // if (!\Yii::$app->user->can($this->listPermission)) {
        //     throw new ForbiddenHttpException('Access denied');
        // }

        $modelName = $this->modelName;

        $where = [];
        
        foreach ($this->readWhere as $param) {    
            $where[$param] = $this->request[$param];
        }

        $historyItems = $modelName::find()
                ->alias('hi')
                ->select([
                    'hi.*',
                    'pricelist_filter_b_id' => 'fb.id'
                ])
                ->innerJoin('billing_uu.pricelist_filter_b_history h', 'h.id = hi.pricelist_filter_b_history_id')
                ->innerJoin('billing_uu.pricelist_filter_b fb', 'fb.pricelist_filter_a_id = h.pricelist_filter_a_id and fb.nnp_filter = hi.nnp_filter_id')
                ->orderBy($this->nameParamName)
                ->andWhere($where)
                ->asArray()
                ->all();

        return $historyItems;
    }
    
    public function actionGet()
    {
        // do_nothing
    }
    
    public function actionList()
    {
        // do_nothing
    }
    
    public function actionSave()
    {
        // do_nothing
    }
    
    public function actionDelete()
    {
        // do_nothing
    }
}
