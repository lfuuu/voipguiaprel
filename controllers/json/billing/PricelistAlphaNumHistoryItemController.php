<?php

namespace app\controllers\json\billing;

use app\classes\JsonController;
use app\models\billing_uu\A2pAlphaNum;
use app\models\billing_uu\A2pAlphaNumHistoryItem;
use app\models\billing_uu\A2pAlphaNumHistory;
use InvalidArgumentException;
use yii\web\ForbiddenHttpException;

class PricelistAlphaNumHistoryItemController extends JsonController
{
    protected $modelName = 'app\models\billing_uu\A2pAlphaNumHistoryItem';
    protected $idParamName = 'id';
    protected $nameParamName = 'alphanum';
    protected $listPermission = 'pricelist_edit';
    protected $readWhere = ['a2p_alphanum_history_id'];

    public function actionRead()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $where = [];

        foreach ($this->readWhere as $param) {
            $where[$param] = $this->request[$param];
        }

        $items =
            $modelName::find()
                ->orderBy($this->nameParamName)
                ->andWhere($where)
                ->asArray()
                ->all();

        $items = $this->performAfterReadActions($items);

        return $items;
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
