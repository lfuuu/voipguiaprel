<?php
namespace app\models;
use app\queries\TrunkPriorityQuery;

/**
 * @property int $id
 * @property int $trunk_id
 * @property int $order
 * @property int $priority
 * @property int $prefixlist_id
 * @property
 */
class TrunkPriority extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.trunk_priority';
    }

    public static function find()
    {
        return new TrunkPriorityQuery(get_called_class());
    }

    public static function create(Trunk $trunk, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->trunk_id = $trunk->id;
        return $item;
    }

    public static function deleteByTrunk(Trunk $trunk)
    {
        return self::deleteAll(['trunk_id' => $trunk->id]);
    }

    public function rules()
    {
        return [
            [['priority'], 'integer', 'min'=> -10, 'max' => 10],
            [['number_id_filter_a','number_id_filter_b'], 'integer'],
        ];
    }
}