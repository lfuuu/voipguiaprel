<?php

namespace app\controllers;

use app\classes\PrefixExpander;
use app\models\Prefixlist;
use Yii;
use app\classes\BaseController;
use app\models\PrefixlistPrefix;

class PrefixlistController extends BaseController
{
    public $enableCsrfValidation = false;

    public function actionShow($id) {
        $list =
            PrefixlistPrefix::find()
                ->select(['prefix'])
                ->where(['prefixlist_id' => $id])
                ->orderBy('prefix')
                ->asArray()
                ->all()
            ;

        header('Content-Type: text/plain');
        foreach ($list as $item) {
            echo $item['prefix'] . "\n";
        }
    }

    public function actionDownload($id) {
        $list =
            PrefixlistPrefix::find()
                ->select(['prefix'])
                ->where(['prefixlist_id' => $id])
                ->orderBy('prefix')
                ->asArray()
                ->all()
        ;

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=prefixlist.csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        foreach ($list as $item) {
            echo $item['prefix'] . "\n";
        }
    }

    public function actionUploadCsv($id) {
        $prefixlist = Prefixlist::findOne($id);
        if ($prefixlist->type_id != 4) {
            throw new \Exception("Prefixlist type must be CSV");
        }

        if (($handle = fopen($_FILES['filedata']['tmp_name'], "r")) !== FALSE) {

            $prefixes = [];
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $prefixes[] = trim($row[0]);
            }
            fclose($handle);

            $data = [];
            foreach (PrefixExpander::expand($prefixes) as $prefix) {
                $data[] = [$prefixlist->id, $prefix];
            }

            $transaction = Prefixlist::getDb()->beginTransaction();
            try {
                PrefixlistPrefix::deleteByPrefixlist($prefixlist);

                if (count($data) > 0) {
                    PrefixlistPrefix::getDb()->createCommand()->batchInsert(PrefixlistPrefix::tableName(),
                        ['prefixlist_id', 'prefix'],
                        $data
                    )->execute();
                }

                $prefixlist->count = PrefixlistPrefix::find()->where(['prefixlist_id' => $prefixlist->id])->count();
                $prefixlist->save();

                $transaction->commit();

                echo json_encode(['data'=>['count'=>$prefixlist->count]]);
            } finally {
                if ($transaction->getIsActive())
                    $transaction->rollBack();
            }

            die();
        }
    }
}
