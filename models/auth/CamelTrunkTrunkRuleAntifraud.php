<?php
namespace app\models\auth;

use app\models\auth\CamelTrunk;
use app\models\Number;

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
class CamelTrunkTrunkRuleAntifraud extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.antifraud_camel_rule';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['number_id_filter_a', 'number_id_filter_b', 'number_id_filter_c', 'ac_mode'], 'integer'],
            [['allow', 'is_orig'], 'boolean'],
            [['object_comment', 'antifrod_system_type'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
        ];
    }

    /**
     * @param CamelTrunk $trunk
     * @param array|null $data
     * @return CamelTrunkTrunkRuleAntifraud
     */
    public static function create(CamelTrunk $trunk, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->trunk_id = $trunk->id;
        return $item;
    }

    /**
     * @param CamelTrunk $trunk
     * @return int
     */
    public static function deleteByTrunk(CamelTrunk $trunk)
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