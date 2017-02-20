<?php

namespace app\models\billing;

use \Yii;
use yii\db\Expression;

class ServiceTrunk extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.service_trunk';
    }

    /**
     * @param int $trunkId
     * @return \yii\db\ActiveRecord[]
     */
    public static function findActualByTrunkId($trunkId)
    {
        return self::find()
            ->where(['trunk_id' => $trunkId])
            ->andWhere('NOW() BETWEEN activation_dt AND expire_dt')
            ->all();
    }

}