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

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.trunk_rule';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['outgoing'], 'boolean'],
            [['prefixlist_id'], 'integer'],
        ];
    }

    /**
     * @return TrunkRuleQuery
     */
    public static function find()
    {
        return new TrunkRuleQuery(get_called_class());
    }

    /**
     * @param Trunk $trunk
     * @param array|null $data
     * @return TrunkRule
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
    public function getPrefixlist()
    {
        return $this->hasOne(Prefixlist::className(), ['id' => 'prefixlist_id']);
    }

}