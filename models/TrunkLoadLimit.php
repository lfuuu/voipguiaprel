<?php
namespace app\models;

/**
 * @property int $id
 * @property int $trunk_id
 * @property int $order
 * @property int $limit_absolute
 * @property int $limit_relative
 * @property int $limit_ratio
 * @property int $number_id_filter_a
 * @property int $number_id_filter_b
 * @property bool $is_orig
 */
class TrunkLoadLimit extends \yii\db\ActiveRecord
{

    const LOAD_LIMIT_TYPE_ABSOLUTE = 1;
    const LOAD_LIMIT_TYPE_RELATIVE = 2;
    
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.trunk_load_limit';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['limit_absolute', 'limit_relative', 'number_id_filter_a', 'number_id_filter_b', 'mode'], 'integer'],
            [['limit_ratio'], 'string'],
            [['is_orig'], 'boolean'],
        ];
    }

    /**
     * @param Trunk $trunk
     * @param array|null $data
     * @return TrunkTrunkRule
     */
    public static function create(Trunk $trunk, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->trunk_id = $trunk->id;
        return $item;
    }

    /**
     * @param Trunk $trunk
     * @return int
     */
    public static function deleteByTrunk(Trunk $trunk)
    {
        return self::deleteAll(['trunk_id' => $trunk->id]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNumberA()
    {
        return $this->hasOne(Number::className(), ['id' => 'number_id_filter_a']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNumberB()
    {
        return $this->hasOne(Number::className(), ['id' => 'number_id_filter_b']);
    }

}