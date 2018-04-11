<?php

namespace app\models;

use app\queries\UplinkQuery;

/**
 * @property int $id
 * @property int $region_id
 * @property int $p_trunk_id
 * @property int $l_trunk_id
 * @property bool $active
 * @property int $active_mode
 * @property string $region_filter
 */
class Uplink extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.uplink';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['region_id', 'p_trunk_id', 'l_trunk_id', 'active_mode'], 'integer'],
            [['region_filter'], 'string'],
            [['active'], 'boolean'],
        ];
    }

    /**
     * @return UplinkQuery
     */
    public static function find()
    {
        return new UplinkQuery(get_called_class());
    }

    /**
     * @param array|null $data
     * @return Uplink
     */
    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }
    
    public static function deleteByRegionId($regionId)
    {
        $result = self::deleteAll(['region_id' => $regionId]);
        return $result;
    }
    
    public static function deleteByPTrunkId($pTrunkId)
    {
        $result = self::deleteAll(['p_trunk_id' => $pTrunkId]);
        return $result;
    }
    
    public static function deleteByLTrunkId($lTrunkId)
    {
        $result = self::deleteAll(['l_trunk_id' => $lTrunkId]);
        return $result;
    }
}
