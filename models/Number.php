<?php

namespace app\models;

use app\classes\ArrayToCsv;
use app\queries\NumberQuery;
use yii\db\Query;

/**
 * @method static Outcome findOne($condition)
 *
 * @property int $id
 * @property int $server_id
 * @property string $name
 * @property int $type_id
 * @property array $prefixlist_ids
 * @property string $object_comment
 * @property bool $sw_share_with_camel
 */
class Number extends \yii\db\ActiveRecord
{
    const STATUS_A_NUMBER = 1;
    const STATUS_B_NUMBER = 2;

    public static function tableName()
    {
        return 'auth.number';
    }

    public static function find()
    {
        return new NumberQuery(get_called_class());
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
            [['name'], 'string', 'max' => 50],
            [['type_id'], 'integer'],
            [['show_in_stat','sw_shared', 'sw_share_with_camel'], 'boolean'],
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
        ];
    }

    public function getPrefixLists()
    {
        if ($this->prefixlist_ids == '{}') return [];

        $list = [];

        if ($this->prefixlist_ids && $this->prefixlist_ids != '{}') {
            foreach(str_getcsv( trim($this->prefixlist_ids, '{}') ) as $value) {
                $list[] = $value;
            }
        }

        return $list;
    }

    public function setPrefixlists(array $list)
    {
        $arrayToCsv = new ArrayToCsv(',');
        $this->prefixlist_ids = '{' . $arrayToCsv->convertLine($list) . '}';
        return $this;
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = parent::toArray($fields, $expand, $recursive);
        $data['prefixlist_ids'] = $this->getPrefixlists();
        return $data;
    }

    /**
     * @return array
     */
    public function findUsagesInRouteTables()
    {
        return
            (new Query)
                ->select(['rt.*'])
                ->distinct()
                ->from(RouteTableRoute::tableName() . ' as rtr')
                ->innerJoin(RouteTable::tableName() . ' as rt', 'rt.id = rtr.route_table_id')
                ->innerJoin(Number::tableName() . ' as n', 'n.id = rtr.a_number_id or n.id = rtr.b_number_id or n.id = rtr.c_number_id')
                ->where('n.id = ' . $this->id)
                ->all();
    }

    /**
     * @return array
     */
    public function findUsagesInTrunkPriority()
    {
        return
            (new Query)
                ->select(['tp.*', 't.name as trunk_name', 'tg.name as trunk_group_name', 'na.name as number_a_name', 'nb.name as number_b_name', 'nc.name as number_c_name'])
                ->distinct()
                ->from(TrunkPriority::tableName() . ' as tp')
                ->innerJoin(Number::tableName() . ' as n', 'n.id = tp.number_id_filter_a or n.id = tp.number_id_filter_b or n.id = tp.number_id_filter_c')
                ->leftJoin(Number::tableName() . ' as na', 'na.id = tp.number_id_filter_a')
                ->leftJoin(Number::tableName() . ' as nb', 'nb.id = tp.number_id_filter_b')
                ->leftJoin(Number::tableName() . ' as nc', 'nc.id = tp.number_id_filter_c')
                ->innerJoin(Trunk::tableName() . ' as t', 't.id = tp.trunk_id')
                ->innerJoin(TrunkGroup::tableName() . ' as tg', 'tg.id = tp.trunk_group_id')
                ->where('n.id = ' . $this->id)
                ->all();
    }

    /**
     * @return array
     */
    public function findUsagesInTrunkRules()
    {
        return
            (new Query)
                ->select(['tr.*', 't.name as trunk_name', 'tg.name as trunk_group_name', 'na.name as number_a_name', 'nb.name as number_b_name', 'nc.name as number_c_name'])
                ->distinct()
                ->from(TrunkTrunkRule::tableName() . ' as tr')
                ->innerJoin(Number::tableName() . ' as n', 'n.id = tr.number_id_filter_a or n.id = tr.number_id_filter_b or n.id = tr.number_id_filter_c')
                ->leftJoin(Number::tableName() . ' as na', 'na.id = tr.number_id_filter_a')
                ->leftJoin(Number::tableName() . ' as nb', 'nb.id = tr.number_id_filter_b')
                ->leftJoin(Number::tableName() . ' as nc', 'nc.id = tr.number_id_filter_c')
                ->innerJoin(Trunk::tableName() . ' as t', 't.id = tr.trunk_id')
                ->innerJoin(TrunkGroup::tableName() . ' as tg', 'tg.id = tr.trunk_group_id')
                ->where('n.id = ' . $this->id)
                ->all();
    }
    
    public function findUsagesInStatRules()
    {
        return
            (new Query)
                ->select(['t.id as trunk_id', 't.name', 't.trunk_name', 't.object_comment'])
                ->distinct()
                ->from('billing.service_trunk_settings as sts')
                ->innerJoin('billing.service_trunk st', 'st.id = sts.trunk_id')
                ->innerJoin(Trunk::tableName() . ' as t', 't.id = st.trunk_id')
                ->where('sts.src_number_id = ' . $this->id . ' or sts.src_number_id = ' . $this->id)
                ->all();
    }
}