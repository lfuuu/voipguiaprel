<?php

namespace app\models;

use yii\db\Query;

/**
 * @property int $id
 * @property int $server_id
 * @property string $name
 * @property bool $sw_shared
 * @property bool $uplink_trunk_group
 * @property
 */
class TrunkGroup extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.trunk_group';
    }

    /**
     * @param Server $server
     * @param array|null $data
     * @return TrunkGroup
     */
    public static function create(Server $server, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->server_id = $server->id;
        return $item;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 32],
            [['sw_shared', 'uplink_trunk_group'], 'boolean'],
            [['server_id',], 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return ['trunks', 'trunk_groups'];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrunks()
    {
        return $this->hasMany(TrunkGroupItem::className(), ['trunk_group_id' => 'id'])->where('trunk_id is not null');
    }

    public function gettrunk_groups()
    {
        return $this->hasMany(TrunkGroupItem::className(), ['trunk_group_id' => 'id'])->where('child_trunk_group_id is not null');
    }

    /**
     * @return array
     */
    public function getTrunksWithGroupIntoRules()
    {
        return
            (new Query)
                ->select([
                    'trunk_id' => 'trunk.id',
                    'trunk_name' => 'trunk.name',
                    'trunk_source_trunk_rule_default_allowed' => 'trunk.source_trunk_rule_default_allowed',
                    'number_a_id' => 'number_a.id',
                    'number_b_id' => 'number_b.id',
                    'number_c_id' => 'number_c.id',
                    'number_a_name' => 'number_a.name',
                    'number_b_name' => 'number_b.name',
                    'number_c_name' => 'number_c.name',
                ])
                ->from(['trunk_rules' => TrunkTrunkRule::tableName()])
                ->innerJoin(['trunk' => Trunk::tableName()], 'trunk.id = trunk_rules.trunk_id')
                ->leftJoin(['number_a' => Number::tableName()], 'number_a.id = trunk_rules.number_id_filter_a')
                ->leftJoin(['number_b' => Number::tableName()], 'number_b.id = trunk_rules.number_id_filter_b')
                ->leftJoin(['number_c' => Number::tableName()], 'number_c.id = trunk_rules.number_id_filter_c')
                ->where(['trunk_rules.trunk_group_id' => $this->id])
                ->all();
    }

    /**
     * @return array
     */
    public function getTrunksWithGroupIntoPriorities()
    {
        return
            (new Query)
                ->select([
                    'trunk_id' => 'trunk.id',
                    'trunk_name' => 'trunk.name',
                    'priority' => 'trunk_priority.priority',
                    'number_a_id' => 'number_a.id',
                    'number_b_id' => 'number_b.id',
                    'number_c_id' => 'number_c.id',
                    'number_a_name' => 'number_a.name',
                    'number_b_name' => 'number_b.name',
                    'number_c_name' => 'number_c.name',
                ])
                ->from(['trunk_priority' => TrunkPriority::tableName()])
                ->innerJoin(['trunk' => Trunk::tableName()], 'trunk.id = trunk_priority.trunk_id')
                ->leftJoin(['number_a' => Number::tableName()], 'number_a.id = trunk_priority.number_id_filter_a')
                ->leftJoin(['number_b' => Number::tableName()], 'number_b.id = trunk_priority.number_id_filter_b')
                ->leftJoin(['number_c' => Number::tableName()], 'number_c.id = trunk_priority.number_id_filter_c')
                ->where(['trunk_priority.trunk_group_id' => $this->id])
                ->all();
    }

}

