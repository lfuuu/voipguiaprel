<?php

namespace app\models\billing;

use \Yii;

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
     * @return self[]
     */
    public static function findActualByTrunkId($trunkId)
    {
        return self::find()
            ->where(['trunk_id' => $trunkId])
            ->andWhere('NOW() BETWEEN activation_dt AND expire_dt')
            ->all();
    }

}