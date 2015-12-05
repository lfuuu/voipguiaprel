<?php

namespace app\controllers\json;

use app\models\TrunkGroup;
use app\models\TrunkGroupItem;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class TrunkGroupController extends JsonController
{
    public function actionList() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            TrunkGroup::find()
                ->select(['id', 'name'])
                ->where(['server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            TrunkGroup::find()
                ->select(['id', 'name'])
                ->where(['server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item =
            TrunkGroup::find()
                ->with('trunks')
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
        if ($item === null) {
            throw new HttpException(404, 'Группа транков не найдена');
        }

        return $item;
    }

    public function actionSave()
    {
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            $trunkGroup = $this->getTrunkGroupOr404($this->request['id']);
        } else {
            $trunkGroup = TrunkGroup::create($server);
        }

        $trunkGroup->load($this->request, '');

        $transaction = TrunkGroup::getDb()->beginTransaction();
        try {
            if (!$trunkGroup->save()) {
                throw new FormValidationException($trunkGroup);
            }

            TrunkGroupItem::deleteByTrunkGroup($trunkGroup);
            foreach ($this->request['trunks'] as $itemData) {
                $item = TrunkGroupItem::create($trunkGroup, $itemData);
                if (!$item->save()) {
                    throw new FormValidationException($trunkGroup);
                }
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }

    public function actionDelete()
    {
        $item = TrunkGroup::findOne($this->request['id']);
        $item->delete();
    }
}
