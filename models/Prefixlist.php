<?php

namespace app\models;

use app\classes\ArrayToCsv;
use app\queries\PrefixlistQuery;

/**
 * @property int $id
 * @property string $name
 * @property int $server_id
 * @property int $type_id
 * @property string $manual_list
 * @property bool $rossvyaz_mob
 * @property string $rossvyaz_country
 * @property string $rossvyaz_region
 * @property string $rossvyaz_city
 * @property int $rossvyaz_country_id
 * @property int $rossvyaz_region_id
 * @property int $rossvyaz_city_id
 * @property string $rossvyaz_operators
 * @property string $rossvyaz_operator_ids
 * @property string $smezhnost_list
 * @property int $network_config_id
 * @property int $count
 * @property
 */
class Prefixlist extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.prefixlist';
    }

    public static function find()
    {
        return new PrefixlistQuery(get_called_class());
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
            [['sw_shared'], 'boolean'],
            [['type_id'], 'integer'],
            [['rossvyaz_country', 'rossvyaz_region', 'rossvyaz_city'], 'string', 'max' => 100],
            [['rossvyaz_country_id', 'rossvyaz_region_id', 'rossvyaz_city_id', 'network_config_id'], 'integer'],
            [['rossvyaz_mob'], 'boolean'],
            [['exclude_operators'], 'boolean'],
        ];
    }

    public function getManualList()
    {
        if ($this->manual_list == '{}') return [];

        $list = [];
        if ($this->manual_list && $this->manual_list != '{}') {
            foreach(str_getcsv( trim($this->manual_list, '{}') ) as $prefix) {
                $list[] = $prefix;
            }
        }

        return $list;
    }

    public function setManualList(array $list)
    {
        sort($list);
        $arrayToCsv = new ArrayToCsv(',');
        $this->manual_list = '{' . $arrayToCsv->convertLine($list) . '}';
        return $this;
    }


    public function getSmezhnostList()
    {
        if ($this->smezhnost_list == '{}') return [];

        $list = [];
        if ($this->smezhnost_list && $this->smezhnost_list != '{}') {
            foreach(str_getcsv( trim($this->smezhnost_list, '{}') ) as $value) {
                $list[] = $value;
            }
        }

        return $list;
    }

    public function setSmezhnostList(array $list)
    {
        sort($list);
        $arrayToCsv = new ArrayToCsv(',');
        $this->smezhnost_list = '{' . $arrayToCsv->convertLine($list) . '}';
        return $this;
    }

    public function getRossvyazOperators()
    {
        $list = [];

        if ($this->rossvyaz_operator_ids && $this->rossvyaz_operator_ids != '{}') {
            foreach(str_getcsv( trim($this->rossvyaz_operator_ids, '{}') ) as $value) {
                $list[] = [
                    'id' => $value,
                    'name' => 'unknown ' . $value,
                ];
            }
        }

        if ($this->rossvyaz_operators && $this->rossvyaz_operators != '{}') {
            $i = 0;
            foreach(str_getcsv( trim($this->rossvyaz_operators, '{}') ) as $value) {
                if ($i < count($list)) {
                    $list[$i]['name'] = $value;
                }
                $i++;
            }
        }

        return $list;
    }

    public function getRossvyazOperatorIds()
    {
        $list = [];

        if ($this->rossvyaz_operator_ids && $this->rossvyaz_operator_ids != '{}') {
            foreach(str_getcsv( trim($this->rossvyaz_operator_ids, '{}') ) as $value) {
                $list[] = $value;
            }
        }

        return $list;
    }

    public function setRossvyazOperators(array $list)
    {
        $list_ids = [];
        $list_names = [];
        foreach ($list as $item) {
            $list_ids[] = $item['id'];
            $list_names[] = $item['name'];
        }
        $arrayToCsv = new ArrayToCsv(',');
        $this->rossvyaz_operator_ids = '{' . $arrayToCsv->convertLine($list_ids) . '}';
        $this->rossvyaz_operators = '{' . $arrayToCsv->convertLine($list_names) . '}';
        return $this;
    }


    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = parent::toArray($fields, $expand, $recursive);
        $data['manual_list'] = $this->getManualList();
        $data['smezhnost_list'] = $this->getSmezhnostList();
        $data['rossvyaz_operators'] = $this->getRossvyazOperators();
        return $data;
    }

}