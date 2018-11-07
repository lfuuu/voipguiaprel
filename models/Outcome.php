<?php

namespace app\models;
use app\queries\OutcomeQuery;
use yii\db\Query;

/**
 * @method static Outcome findOne($condition)
 *
 * @property int $id
 * @property string $name
 * @property int $type_id
 * @property int $route_case_id
 * @property RouteCase $route_case
 * @property int $release_reason_id
 * @property ReleaseReason $release_reason
 * @property int $airp_id
 * @property ReleaseReason $airp
 * @property int $calling_station_id
 * @property int $called_station_id
 * @property string $header
 * @property
 */
class Outcome extends \yii\db\ActiveRecord
{
    const TYPE_AUTO = 1;
    const TYPE_ROUTE_CASE = 2;
    const TYPE_RELEASE_REASON = 3;
    const TYPE_AIRP = 4;
    const TYPE_ACCEPT = 4;

    public static function tableName()
    {
        return 'auth.outcome';
    }

    public static function find()
    {
        return new OutcomeQuery(get_called_class());
    }

    public static function create(Server $server, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->server_id = $server->id;
        return $item;
    }

    public function rules()
    {
        return [
            [['name','rn','ocpn', 'header'], 'string', 'max' => 50],
            [['type_id'], 'integer'],
            [['sw_shared'], 'boolean'],
            [['route_case_id', 'release_reason_id', 'airp_id','route_case_1_id','route_case_2_id'], 'integer'],
            [['calling_station_id', 'called_station_id'], 'match', 'pattern' => '/^\d{1,20}$/'],
        ];
    }

    public function extraFields()
    {
        return ['route_case', 'release_reason', 'airp'];
    }

    public function getRouteCase()
    {
        return $this->hasOne(RouteCase::className(), ['id' => 'route_case_id']);
    }

    public function getReleaseReason()
    {
        return $this->hasOne(ReleaseReason::className(), ['id' => 'release_reason_id']);
    }

    public function getAirp()
    {
        return $this->hasOne(Airp::className(), ['id' => 'airp_id']);
    }

    /**
     * @return array
     */
    public function findUsagesInRouteTables()
    {
        return
            (new Query)
                ->select(['rt.*'])
                ->from(RouteTableRoute::tableName() . ' as rtr')
                ->innerJoin(RouteTable::tableName() . ' as rt', 'rt.id = rtr.route_table_id')
                ->innerJoin(Outcome::tableName() . ' as o', 'o.id = rtr.outcome_id')
                ->where('o.id = ' . $this->id)
                ->all();
    }
}