<?php

namespace app\models;

use app\queries\TrunkQuery;
use \app\models\sorm\Trunk as TrunkSorm;
use yii\db\Query;

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
 * @property bool $source_trunk_rule_default_allowed
 * @property int $capacity
 * @property bool $sw_minimalki
 * @property bool $sw_shared
 * @property int $load_warning
 * @property bool $tech_trunk
 * @property string $road_to_regions
 * @property bool $pstn_trunk
 * @property bool $mgmn_trunk
 * @property bool $orig_afilter_default_allowed
 * @property bool $orig_bfilter_default_allowed
 * @property bool $term_afilter_default_allowed
 * @property bool $term_bfilter_default_allowed
 * @property bool $roaming_orig
 * @property bool $roaming_term
 * @property int $id_pbx
 * @property string $back_trunk
 * @property int $location_id
 * @property bool $mgmn2_orig
 * @property bool $mgmn2_term
 *
 * @property \yii\db\ActiveQuery rulesSourceOrig
 * @property \yii\db\ActiveQuery rulesDestinationOrig
 * @property \yii\db\ActiveQuery rulesSourceTerm
 * @property \yii\db\ActiveQuery rulesDestinationTerm
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
            [[
                'auto_routing', 'source_rule_default_allowed', 'destination_rule_default_allowed',
                'source_trunk_rule_default_allowed', 'our_trunk', 'auth_by_number', 'orig_redirect_number_7800',
                'orig_redirect_number', 'term_redirect_number', 'show_in_stat', 'sw_minimalki', 'sw_shared',
                'tech_trunk', 'pstn_trunk', 'mgmn_trunk','mgmn_orig_trunk','le8accept',
                'orig_afilter_default_allowed', 'orig_bfilter_default_allowed',
                'term_afilter_default_allowed', 'term_bfilter_default_allowed',
                'roaming_orig', 'roaming_term', 'mgmn2_orig', 'mgmn2_term'
            ], 'boolean'],
            [['route_table_id', 'capacity', 'load_warning', 'id_pbx', 'location_id'], 'integer'],
            [['back_trunk'], 'string', 'max' => 50],
            [['road_to_regions', 'trace_to_regions'], 'string', 'max' => 100],
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
    public function getTrunkSorm()
    {
        return $this->hasMany(TrunkSorm::className(), ['code_trunk' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLoadLimit()
    {
        return $this->hasMany(TrunkLoadLimit::className(), ['trunk_id' => 'id'])->orderBy('order');
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
    public function getNumbersRules(array $where = null)
    {
        $link = $this
            ->hasMany(TrunkABfiltersRule::className(), ['trunk_id' => 'id'])
            ->orderBy('order');
        return (!is_null($where) ? $link->andWhere($where) : $link);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRulesSourceOrig()
    {
        return $this->getNumbersRules([
            'orig' => true,
            'outgoing' => false,
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRulesSourceTerm()
    {
        return $this->getNumbersRules([
            'orig' => false,
            'outgoing' => false,
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRulesDestinationOrig()
    {
        return $this->getNumbersRules([
            'orig' => true,
            'outgoing' => true,
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRulesDestinationTerm()
    {
        return $this->getNumbersRules([
            'orig' => false,
            'outgoing' => true,
        ]);
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
    
    /**
     * @return array
     */
    public function findUsagesInTrunkGroups()
    {
        return
            (new Query())
                ->select(['tg.*'])
                ->distinct()
                ->from(TrunkGroup::tableName() . ' as tg')
                ->innerJoin(TrunkGroupItem::tableName() . ' as tgi', 'tgi.trunk_group_id = tg.id')
                ->where('tgi.trunk_id = ' . $this->id)
                ->all();
    }
}
