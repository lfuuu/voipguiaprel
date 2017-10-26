<?php
namespace app\models;

/**
 * @property int $trunk_group_id
 * @property int $trunk_id
 * @property
 */
class TrunkGroupItem extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.trunk_group_item';
    }

    public static function create(TrunkGroup $group, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->trunk_group_id = $group->id;
        return $item;
    }

    public function rules()
    {
        return [
            [['trunk_id'], 'integer'],
            [['child_trunk_group_id'], 'integer']
        ];
    }

    public static function deleteByTrunkGroup(TrunkGroup $group)
    {
        return self::deleteAll(['trunk_group_id' => $group->id]);
    }

}