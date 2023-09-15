<?php
namespace app\models;

/**
 * @property int $id
 * @property int $trunk_id
 * @property int $order
 * @property int $trunk_group_id
 * @property int $number_id_filter_a
 * @property int $number_id_filter_b
 * @property int $number_id_filter_c
 * @property bool $allow
 * @property bool $is_orig
 * @property string $object_comment
 * @property string $antifrod_system_type
 * @property 
 */
class TrunkTrunkRuleAntifraud extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.antifrod_trunk_rule';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['trunk_group_id', 'number_id_filter_a', 'number_id_filter_b', 'number_id_filter_c', 'ac_mode'], 'integer'],
            [['allow', 'is_orig'], 'boolean'],
            [['object_comment', 'antifrod_system_type'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
        ];
    }

    /**
     * @param Trunk $trunk
     * @param array|null $data
     * @return TrunkTrunkRuleAntifraud
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