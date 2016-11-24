<?php
namespace app\models;

/**
 * @property int $id
 * @property int $trunk_id
 * @property int $order
 * @property int $trunk_group_id
 * @property number_id_filter_a
 * @property number_id_filter_b
 * @property
 */
class TrunkTrunkRule extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.trunk_trunk_rule';
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
            [['trunk_group_id'], 'integer'],
            [['number_id_filter_a'], 'integer'],
            [['number_id_filter_b'], 'integer'],
        ];
    }
}