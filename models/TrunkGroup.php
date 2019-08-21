<?php

namespace app\models;

use app\models\auth\OutcomeRule;
use yii\db\Query;

/**
 * @property int $id
 * @property int $server_id
 * @property string $name
 * @property bool $sw_shared
 * @property bool $uplink_trunk_group
 * @property string $object_comment
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
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
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
                    'id' => 'trunk_rules.id',
                    'trunk_id' => 'trunk.id',
                    'trunk_name' => 'trunk.name',
                    'trunk_source_trunk_rule_default_allowed' => 'trunk.source_trunk_rule_default_allowed',
                    'number_a_id' => 'number_a.id',
                    'number_b_id' => 'number_b.id',
                    'number_c_id' => 'number_c.id',
                    'number_a_name' => 'number_a.name',
                    'number_b_name' => 'number_b.name',
                    'number_c_name' => 'number_c.name',
                    'object_comment' => 'trunk_rules.object_comment'
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
                    'id' => 'trunk_priority.id',
                    'trunk_id' => 'trunk.id',
                    'trunk_name' => 'trunk.name',
                    'priority' => 'trunk_priority.priority',
                    'number_a_id' => 'number_a.id',
                    'number_b_id' => 'number_b.id',
                    'number_c_id' => 'number_c.id',
                    'number_a_name' => 'number_a.name',
                    'number_b_name' => 'number_b.name',
                    'number_c_name' => 'number_c.name',
                    'object_comment' => 'trunk_priority.object_comment'
                ])
                ->from(['trunk_priority' => TrunkPriority::tableName()])
                ->innerJoin(['trunk' => Trunk::tableName()], 'trunk.id = trunk_priority.trunk_id')
                ->leftJoin(['number_a' => Number::tableName()], 'number_a.id = trunk_priority.number_id_filter_a')
                ->leftJoin(['number_b' => Number::tableName()], 'number_b.id = trunk_priority.number_id_filter_b')
                ->leftJoin(['number_c' => Number::tableName()], 'number_c.id = trunk_priority.number_id_filter_c')
                ->where(['trunk_priority.trunk_group_id' => $this->id])
                ->all();
    }
    
    public function getRouteTablesWithGroup()
    {
        return
            (new Query)
                ->select([
                    'id' => 'rt.id',
                    'name' => 'rt.name',
                    'server_id' =>'rt.server_id',
                    'object_comment' => 'rt.object_comment'
                ])
                ->distinct()
                ->from(['rt' => RouteTable::tableName()])
                ->innerJoin(['rrr' => RouteRouteRule::tableName()], 'rrr.route_table_id = rt.id')
                ->where(['rrr.trunk_group_id' => $this->id])
                ->all();
    }
    
    public function getOutcomesWithGroup()
    {
        return
            (new Query)
                ->select([
                    'id' => 'o.id',
                    'name' => 'o.name',
                    'server_id' =>'o.server_id',
                    'object_comment' => 'o.object_comment'
                ])
                ->distinct()
                ->from(['o' => Outcome::tableName()])
                ->innerJoin(['oru' => OutcomeRule::tableName()], 'oru.outcome_id = o.id')
                ->where(['oru.trunk_group_id' => $this->id])
                ->all();
    }
    
    public function getGroupsWithGroup()
    {
        return
            (new Query)
                ->select([
                    'id' => 'tg.id',
                    'name' => 'tg.name',
                    'server_id' =>'tg.server_id',
                    'object_comment' => 'tg.object_comment'
                ])
                ->distinct()
                ->from(['tg' => self::tableName()])
                ->innerJoin(['tgi' => TrunkGroupItem::tableName()], 'tgi.trunk_group_id = tg.id')
                ->where(['tgi.child_trunk_group_id' => $this->id])
                ->all();
    }
}

