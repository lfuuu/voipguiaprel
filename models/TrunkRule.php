<?php
namespace app\models;
use app\queries\TrunkRuleQuery;

/**
 * @property int $id
 * @property int $trunk_id
 * @property bool $outgoing
 * @property int $order
 * @property int $prefixlist_id
 * @property
 */
class TrunkRule extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.trunk_rule';
    }

    public static function find()
    {
        return new TrunkRuleQuery(get_called_class());
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
            [['outgoing'], 'boolean'],
            [['prefixlist_id'], 'integer'],
        ];
    }
}