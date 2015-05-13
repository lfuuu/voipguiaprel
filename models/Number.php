<?php

namespace app\models;

use app\classes\ArrayToCsv;
use app\queries\NumberQuery;

/**
 * @property int $id
 * @property int $server_id
 * @property string $name
 * @property int $type_id
 * @property array $prefixlist_ids
 * @property int $cpc_id
 * @property
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
            [['show_in_stat'], 'boolean'],
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

}