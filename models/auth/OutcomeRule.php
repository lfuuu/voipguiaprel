<?php
namespace app\models\auth;

use app\models\Number;
use app\models\Outcome;
use app\models\TrunkGroup;

/**
 * @property int $id
 * @property int $server_id
 * @property int $outcome_id
 * @property int $order
 * @property int $trunk_group_id
 * @property int $number_id_filter_a
 * @property int $number_id_filter_b
 * @property bool $allow
 */
class OutcomeRule extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.outcome_rule';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['trunk_group_id', 'number_id_filter_a', 'number_id_filter_b', 'server_id'], 'integer'],
            [['allow'], 'boolean'],
        ];
    }

    /**
     * @param Outcome $outcome
     * @param array|null $data
     * @return OutcomeRule
     */
    public static function create(Outcome $outcome, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->outcome_id = $outcome->id;
        return $item;
    }

    /**
     * @param Trunk $trunk
     * @return int
     */
    public static function deleteByOutcome(Outcome $outcome)
    {
        return self::deleteAll(['outcome_id' => $outcome->id]);
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