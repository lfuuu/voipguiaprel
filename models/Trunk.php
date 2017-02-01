<?php

namespace app\models;

use app\queries\TrunkQuery;

/**
 * @property int $id
 * @property int $server_id
 * @property string $name
 * @property bool $source_rule_default_allowed
 * @property bool $destination_rule_default_allowed
 * @property int $default_priority
 * @property string $trunk_name
 * @property bool $auto_routing
 * @property bool $our_trunk
 * @property int $route_table_id
 * @property bool $orig_redirect_number_7800
 * @property bool $orig_redirect_number
 * @property bool $term_redirect_number
 * @property
 */
class Trunk extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.trunk';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 50],
            [['trunk_name','trunk_name_alias'], 'string', 'max' => 32],
            [['default_priority'], 'integer', 'min'=> -10, 'max' => 10],
            [['auto_routing', 'source_rule_default_allowed', 'destination_rule_default_allowed', 'source_trunk_rule_default_allowed','megatrunk_transfer_to_region','megatrunk_transfer_to_megatrunk',
                'our_trunk','auth_by_number','orig_redirect_number_7800','orig_redirect_number','term_redirect_number','show_in_stat','sw_minimalki','sw_shared'], 'boolean'],
            [['route_table_id','capacity','load_warning','road_to_region'], 'integer'],
        ];
    }

    /**
     * @return TrunkQuery
     */
    public static function find()
    {
        return new TrunkQuery(get_called_class());
    }

    /**
     * @param Server $server
     * @param array|null $data
     * @return Trunk
     */
    public static function create(Server $server, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->server_id = $server->id;
        $item->default_priority = 0;
        return $item;
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return ['routeTable', 'priorities', 'rules', 'trunkRules', 'numberPreprocessing'];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServer()
    {
        return $this->hasOne(Server::className(), ['id' => 'server_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrunkOrigTerm()
    {
        return $this->hasOne(TrunkOrigTerm::className(), ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRouteTable()
    {
        return $this->hasOne(RouteTable::className(), ['id' => 'route_table_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPriorities()
    {
        return $this->hasMany(TrunkPriority::className(), ['trunk_id' => 'id'])->orderBy('order');
    }

    /**
     * @param array $where
     * @return \yii\db\ActiveQuery
     */
    public function getRules(array $where = null)
    {
        $link = $this->hasMany(TrunkRule::className(), ['trunk_id' => 'id'])->orderBy('order');
        return ($where !== null ? $link->andWhere($where) : $link);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRulesSource()
    {
        return $this->getRules(['outgoing' => false]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRulesDestination()
    {
        return $this->getRules(['outgoing' => true]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrunkRules()
    {
        return $this->hasMany(TrunkTrunkRule::className(), ['trunk_id' => 'id'])->orderBy('order');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNumberPreprocessing()
    {
        return $this->hasMany(TrunkNumberPreprocessing::className(), ['trunk_id' => 'id'])->orderBy('order');
    }

}
