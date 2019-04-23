<?php
namespace app\models;

use app\queries\TrunkPriorityQuery;

/**
 * @property int $id
 * @property int $trunk_id
 * @property int $order
 * @property int $priority
 * @property int $prefixlist_id
 * @property int $number_id_filter_a
 * @property int $number_id_filter_b
 * @property int $number_id_filter_c
 * @property int $trunk_group_id
 * @property string $object_comment
 */
class TrunkPriority extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.trunk_priority';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['priority'], 'integer', 'min'=> -10, 'max' => 10],
            [['number_id_filter_a','number_id_filter_b','number_id_filter_c','trunk_group_id'], 'integer'],
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
        ];
    }

    /**
     * @return TrunkPriorityQuery
     */
    public static function find()
    {
        return new TrunkPriorityQuery(get_called_class());
    }

    /**
     * @param Trunk $trunk
     * @param array|null $data
     * @return TrunkPriority
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
    public function getTrunkGroup()
    {
        return $this->hasOne(TrunkGroup::className(), ['id' => 'trunk_group_id']);
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