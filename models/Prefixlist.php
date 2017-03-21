<?php

namespace app\models;

use app\classes\ArrayToCsv;
use app\queries\PrefixlistQuery;
use yii\helpers\Json;

/**
 * @property int $id
 * @property int $server_id
 * @property string $name
 * @property string $manual_list
 * @property int $type_id
 * @property bool $rossvyaz_mob
 * @property string $rossvyaz_country
 * @property string $rossvyaz_region
 * @property string $rossvyaz_city
 * @property int $rossvyaz_country_id
 * @property int $rossvyaz_region_id
 * @property int $rossvyaz_city_id
 * @property string[] $rossvyaz_operators
 * @property int[] $rossvyaz_operator_ids
 * @property int $count
 * @property bool $exclude_operators
 * @property int[] $smezhnost_list
 * @property int $network_config_id
 * @property bool $sw_shared
 * @property string $nnp_filter_json
 */
class Prefixlist extends \yii\db\ActiveRecord
{

    const PREFIXLIST_TYPE_MANUAL = 1; // Вручную
    const PREFIXLIST_TYPE_LOCAL_PREFIXES = 2; // Местные префиксы
    const PREFIXLIST_TYPE_ROSSVYAZ = 3; // РосСвязь
    const PREFIXLIST_TYPE_CSV = 4; // CSV
    const PREFIXLIST_TYPE_DEARLY_CODES = 5; // Дорогие коды
    const PREFIXLIST_TYPE_NNP = 6; // ННП

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.prefixlist';
    }

    /**
     * @return PrefixlistQuery
     */
    public static function find()
    {
        return new PrefixlistQuery(get_called_class());
    }

    /**
     * @param Server $server
     * @param array $data
     * @return Prefixlist
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
            [['name'], 'string', 'max' => 50],
            [['sw_shared'], 'boolean'],
            [['type_id'], 'integer'],
            [['rossvyaz_country', 'rossvyaz_region', 'rossvyaz_city'], 'string', 'max' => 100],
            [['rossvyaz_country_id', 'rossvyaz_region_id', 'rossvyaz_city_id', 'network_config_id'], 'integer'],
            [['rossvyaz_mob'], 'boolean'],
            [['exclude_operators'], 'boolean'],
            ['nnp_filter_json', 'string'],
        ];
    }

    /**
     * @return array
     */
    public function getManualList()
    {
        // ActiveRecord don't know about PGSQL array type fields
        if ($this->manual_list == '{}') {
            return [];
        }

        $list = [];
        foreach (str_getcsv( trim($this->manual_list, '{}')) as $prefix) {
            $list[] = $prefix;
        }

        return $list;
    }

    /**
     * @param array $list
     * @return $this
     */
    public function setManualList(array $list)
    {
        sort($list);
        $arrayToCsv = new ArrayToCsv(',');
        $this->manual_list = '{' . $arrayToCsv->convertLine($list) . '}';
        return $this;
    }

    /**
     * @return array
     */
    public function getSmezhnostList()
    {
        // ActiveRecord don't know about PGSQL array type fields
        if ($this->smezhnost_list == '{}') {
            return [];
        }

        $list = [];
        foreach (str_getcsv(trim($this->smezhnost_list, '{}')) as $value) {
            $list[] = $value;
        }

        return $list;
    }

    /**
     * @param array $list
     * @return $this
     */
    public function setSmezhnostList(array $list)
    {
        sort($list);
        $arrayToCsv = new ArrayToCsv(',');
        $this->smezhnost_list = '{' . $arrayToCsv->convertLine($list) . '}';
        return $this;
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @param array $list
     * @return $this
     */
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

    /**
     * @param array $input
     * @return $this
     */
    public function setNnpFilters(array $input)
    {
        $filters = [
            'nnp_destination_id' => isset($input['nnp_destination']) ? $input['nnp_destination'] : '',
            'country_code' => isset($input['nnp_country']) ? $input['nnp_country'] : '',
            'region_id' => isset($input['nnp_region']) ? $input['nnp_region'] : '',
            'city_id' => isset($input['nnp_city']) ? $input['nnp_city'] : '',
            'operator_id' => isset($input['nnp_operator']) ? $input['nnp_operator'] : '',
            'ndc_type_id' => isset($input['nnp_ndc_type']) ? $input['nnp_ndc_type'] : '',
        ];
        $this->nnp_filter_json = Json::encode($filters);
        return $this;
    }

    /**
     * @param array $fields
     * @param array $expand
     * @param bool $recursive
     * @return array
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = parent::toArray($fields, $expand, $recursive);
        $data['manual_list'] = $this->getManualList();
        $data['smezhnost_list'] = $this->getSmezhnostList();
        $data['rossvyaz_operators'] = $this->getRossvyazOperators();
        return $data;
    }

}