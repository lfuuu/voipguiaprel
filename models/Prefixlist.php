<?php

namespace app\models;

use app\classes\ArrayToCsv;
use app\queries\PrefixlistQuery;
use yii\helpers\Json;
use yii\db\Query;

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
 * @property bool $invert
 * @property bool $is_protection_disabled
 * @property bool $normalization_disabled
 * @property string $object_comment
 * @property bool $sw_share_with_camel
 * @property bool $is_emergency
 * @property bool $strong_matching
 * @property bool $is_use_ported
 *
 * @property PrefixlistPrefix $prefixlistPrefix
 */
class Prefixlist extends \yii\db\ActiveRecord
{

    const PREFIXLIST_TYPE_MANUAL = 1; // Вручную
    const PREFIXLIST_TYPE_LOCAL_PREFIXES = 2; // Местные префиксы
    const PREFIXLIST_TYPE_ROSSVYAZ = 3; // РосСвязь
    const PREFIXLIST_TYPE_CSV = 4; // CSV
    const PREFIXLIST_TYPE_DEARLY_CODES = 5; // Дорогие коды
    const PREFIXLIST_TYPE_NNP = 6; // ННП
    const PREFIXLIST_TYPE_7800 = 7; // 7800
    const PREFIXLIST_TYPE_DID_ON_VPBX = 8; // Дид на ВАТС
    const PREFIXLIST_TYPE_FMC = 9; // FMC
    const PREFIXLIST_TYPE_PARTED_NUM = 10; // Parted num
    const PREFIXLIST_TYPE_ROAMING = 11; // Роуминг
    const PREFIXLIST_TYPE_VOIP_REGISTRY = 12; // Реестр номеров
    const PREFIXLIST_TYPE_VOIP_NUMBER = 13; // Номера
    const PREFIXLIST_TYPE_GT = 14; // GT
    const PREFIXLIST_TYPE_RN = 15; // Routing Number

    public $_subitems = [
        'prefixlistPrefix' => 'getPrefixlistPrefix',
        'prefixlistPrefixPrepare' => 'getPrefixlistPrefixPrepare'
    ];
    
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.prefixlist';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 50],
            [['sw_shared', 'sw_share_with_camel', 'strong_matching'], 'boolean'],
            [['type_id'], 'integer'],
            [['rossvyaz_country', 'rossvyaz_region', 'rossvyaz_city'], 'string', 'max' => 100],
            [['rossvyaz_country_id', 'rossvyaz_region_id', 'rossvyaz_city_id', 'network_config_id'], 'integer'],
            [['rossvyaz_mob', 'normalization_disabled'], 'boolean'],
            [['exclude_operators'], 'boolean'],
            ['nnp_filter_json', 'string'],
            [['is_global', 'is_use_ported'], 'boolean'],
            [['is_auto_update', 'is_protection_disabled'], 'boolean'],
            [['is_emergency'], 'boolean'],
            [['invert'], 'boolean'],
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
        ];
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
     * @return \yii\db\ActiveQuery
     */
    public function getPrefixlistPrefix()
    {
        return $this->hasMany(PrefixlistPrefix::className(), ['prefixlist_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrefixlistPrefixPrepare()
    {
        return $this->hasMany(PrefixlistPrefixPrepare::className(), ['prefixlist_id' => 'id']);
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
        $token = null;
        
        if (!empty($this->nnp_filter_json)) {
            $nnpFilter = json_decode($this->nnp_filter_json, true);
        
            if (!empty($nnpFilter['token'])) {
                $token = $nnpFilter['token'];
            }
        }
        
        $filters = [
            'is_default' => isset($input['nnp_is_default']) ? $input['nnp_is_default'] : '',
            'use_nnp_ported' => isset($input['nnp_use_nnp_ported']) ? $input['nnp_use_nnp_ported'] : '',
            'token' => $token ? $token : bin2hex(openssl_random_pseudo_bytes(16)),
        ];
        
        if (isset($input['nnp_operator']) && count($input['nnp_operator'])) {
            $filters['operator_id'] = $input['nnp_operator'];
            $filters['exclude_operators'] = array_key_exists('nnp_is_exclude_operators', $input) ?
                $input['nnp_is_exclude_operators'] :
                '';
        }
    
        if (isset($input['nnp_country']) && count($input['nnp_country'])) {
            $filters['country_code'] = $input['nnp_country'];
            $filters['exclude_country'] = array_key_exists('nnp_is_exclude_country', $input) ?
                $input['nnp_is_exclude_country'] :
                '';
        }
    
        if (isset($input['nnp_region']) && count($input['nnp_region'])) {
            $filters['region_id'] = $input['nnp_region'];
            $filters['exclude_region'] = array_key_exists('nnp_is_exclude_region', $input) ?
                $input['nnp_is_exclude_region'] :
                '';
        }
    
        if (isset($input['nnp_city']) && count($input['nnp_city'])) {
            $filters['city_id'] = $input['nnp_city'];
            $filters['exclude_city'] = array_key_exists('nnp_is_exclude_city', $input) ?
                $input['nnp_is_exclude_city'] :
                '';
        }
    
        if (isset($input['nnp_ndc_type']) && count($input['nnp_ndc_type'])) {
            $filters['ndc_type_id'] = $input['nnp_ndc_type'];
            $filters['exclude_ndc_type'] = array_key_exists('nnp_is_exclude_ndc_type', $input) ?
                $input['nnp_is_exclude_ndc_type'] :
                '';
        }

        if (isset($input['nnp_ndc']) && count($input['nnp_ndc'])) {
            $filters['ndc'] = $input['nnp_ndc'];
            $filters['exclude_ndc'] = array_key_exists('nnp_is_exclude_ndc', $input) ?
                $input['nnp_is_exclude_ndc'] :
                '';
        }
    
        if (isset($input['nnp_destination']) && count($input['nnp_destination'])) {
            $filters['nnp_destination_id'] = $input['nnp_destination'];
            $filters['exclude_destination'] = array_key_exists('nnp_is_exclude_destination', $input) ?
                $input['nnp_is_exclude_destination'] :
                '';
        }

        $this->nnp_filter_json = Json::encode($filters);
        return $this;
    }
    
    /**
     * @param array $input
     * @return $this
     */
    public function setVoipRegistryFilters(array $input)
    {
        $token = null;
        
        if (!empty($this->nnp_filter_json)) {
            $nnpFilter = json_decode($this->nnp_filter_json, true);
            
            if (!empty($nnpFilter['token'])) {
                $token = $nnpFilter['token'];
            }
        }
        
        $filters = [
            'country_code' => isset($input['registry_country']) ? $input['registry_country'] : '',
            'city_id' => isset($input['registry_city']) ? $input['registry_city'] : '',
            'ndc_type_id' => isset($input['registry_ndc_type']) ? $input['registry_ndc_type'] : '',
            'source' => isset($input['registry_source']) ? $input['registry_source'] : '',
            'token' => $token ? $token : bin2hex(openssl_random_pseudo_bytes(16)),
        ];
        
        $this->nnp_filter_json = Json::encode($filters);
        return $this;
    }
    
    /**
     * @param array $input
     * @return $this
     */
    public function setVoipNumberFilters(array $input)
    {
        $token = null;
        
        if (!empty($this->nnp_filter_json)) {
            $nnpFilter = json_decode($this->nnp_filter_json, true);
            
            if (!empty($nnpFilter['token'])) {
                $token = $nnpFilter['token'];
            }
        }
        
        $filters = [
            'country_code' => isset($input['number_country']) ? $input['number_country'] : '',
            'region_id' => isset($input['number_region']) ? $input['number_region'] : '',
            'city_id' => isset($input['number_city']) ? $input['number_city'] : '',
            'ndc_type_id' => isset($input['number_ndc_type']) ? $input['number_ndc_type'] : '',
            'source' => isset($input['number_source']) ? $input['number_source'] : '',
            'status' => isset($input['number_status']) ? $input['number_status'] : '',
            'exclude_country_code' => isset($input['number_exclude_country']) ? $input['number_exclude_country'] : '',
            'exclude_region_id' => isset($input['number_exclude_region']) ? $input['number_exclude_region'] : '',
            'exclude_city_id' => isset($input['number_exclude_city']) ? $input['number_exclude_city'] : '',
            'exclude_ndc_type_id' => isset($input['number_exclude_ndc_type']) ? $input['number_exclude_ndc_type'] : '',
            'exclude_source' => isset($input['number_exclude_source']) ? $input['number_exclude_source'] : '',
            'exclude_status' => isset($input['number_exclude_status']) ? $input['number_exclude_status'] : '',
            'token' => $token ? $token : bin2hex(openssl_random_pseudo_bytes(16)),
        ];

        if (isset($input['number_statuses']) && count($input['number_statuses'])) {
            $filters['statuses'] = $input['number_statuses'];
            $filters['exclude_statuses'] = array_key_exists('number_exclude_statuses', $input) ?
                $input['number_exclude_statuses'] :
                '';
        }

        if (isset($input['number_countries']) && count($input['number_countries'])) {
            $filters['country_codes'] = $input['number_countries'];
            $filters['exclude_country_codes'] = array_key_exists('number_exclude_countries', $input) ?
                $input['number_exclude_countries'] :
                '';
        }
        if (isset($input['number_regions']) && count($input['number_regions'])) {
            $filters['region_ids'] = $input['number_regions'];
            $filters['exclude_region_ids'] = array_key_exists('number_exclude_regions', $input) ?
                $input['number_exclude_regions'] :
                '';
        }
        if (isset($input['number_cities']) && count($input['number_cities'])) {
            $filters['city_ids'] = $input['number_cities'];
            $filters['exclude_city_ids'] = array_key_exists('number_exclude_cities', $input) ?
                $input['number_exclude_cities'] :
                '';
        }

        if (isset($input['number_ndc_types']) && count($input['number_ndc_types'])) {
            $filters['ndc_type_ids'] = $input['number_ndc_types'];
            $filters['exclude_ndc_type_ids'] = array_key_exists('number_exclude_ndc_types', $input) ?
                $input['number_exclude_ndc_types'] :
                '';
        }
        
        if (isset($input['number_sources']) && count($input['number_sources'])) {
            $filters['sources'] = $input['number_sources'];
            $filters['exclude_sources'] = array_key_exists('number_exclude_sources', $input) ?
                $input['number_exclude_sources'] :
                '';
        }
        
        $this->nnp_filter_json = Json::encode($filters);
        return $this;
    }
    
    /**
     * @param array $input
     * @return $this
     */
    public function setPbxFilters(array $input)
    {
        $token = null;
        
        if (!empty($this->nnp_filter_json)) {
            $nnpFilter = json_decode($this->nnp_filter_json, true);
            
            if (!empty($nnpFilter['token'])) {
                $token = $nnpFilter['token'];
            }
        }
        
        $filters = [
            'token' => $token ? $token : bin2hex(openssl_random_pseudo_bytes(16)),
            'pbx_list' => isset($input['pbx_list']) ? $input['pbx_list'] : '',
            'servers' => isset($input['servers']) ? $input['servers'] : ''
        ];
        
        $this->nnp_filter_json = Json::encode($filters);
        return $this;
    }
    
    /**
     * @param array $input
     * @return $this
     */
    public function setTrunkRoamingFilters(array $input)
    {
        $token = null;
        
        if (!empty($this->nnp_filter_json)) {
            $nnpFilter = json_decode($this->nnp_filter_json, true);
            
            if (!empty($nnpFilter['token'])) {
                $token = $nnpFilter['token'];
            }
        }
        
        $filters = [
            'token' => $token ? $token : bin2hex(openssl_random_pseudo_bytes(16)),
            'trunk_list' => isset($input['trunk_roaming_list']) ? $input['trunk_roaming_list'] : '',
            'servers' => isset($input['servers']) ? $input['servers'] : ''
        ];
        
        $this->nnp_filter_json = Json::encode($filters);
        return $this;
    }
    
    public function setFmcFilters(array $input)
    {
        $token = null;
        
        if (!empty($this->nnp_filter_json)) {
            $nnpFilter = json_decode($this->nnp_filter_json, true);
            
            if (!empty($nnpFilter['token'])) {
                $token = $nnpFilter['token'];
            }
        }
        
        $filters = [
            'token' => $token ? $token : bin2hex(openssl_random_pseudo_bytes(16)),
            'fmc_trunk' => isset($input['fmc_trunk']) ? $input['fmc_trunk'] : ''
        ];
        
        $this->nnp_filter_json = Json::encode($filters);
        return $this;
    }

    /**
     * @param array $input
     * @return $this
     */
    public function setGtFilters(array $input)
    {
        $token = null;

        if (!empty($this->nnp_filter_json)) {
            $nnpFilter = json_decode($this->nnp_filter_json, true);

            if (!empty($nnpFilter['token'])) {
                $token = $nnpFilter['token'];
            }
        }

        $filters = [
            'status' => isset($input['number_status']) ? $input['number_status'] : '',
            'token' => $token ? $token : bin2hex(openssl_random_pseudo_bytes(16)),
        ];

        if (isset($input['gt_country']) && count($input['gt_country'])) {
            $filters['country_code'] = $input['gt_country'];
            $filters['exclude_country'] = array_key_exists('gt_is_exclude_country', $input) ?
                $input['gt_is_exclude_country'] : '';
        }

        if (isset($input['gt_region']) && count($input['gt_region'])) {
            $filters['region_id'] = $input['gt_region'];
            $filters['exclude_region'] = array_key_exists('gt_is_exclude_region', $input) ?
                $input['gt_is_exclude_region'] : '';
        }

        if (isset($input['gt_operator']) && count($input['gt_operator'])) {
            $filters['operator_id'] = $input['gt_operator'];
            $filters['exclude_operators'] = array_key_exists('gt_is_exclude_operators', $input) ?
                $input['gt_is_exclude_operators'] : '';
        }

        $this->nnp_filter_json = Json::encode($filters);
        return $this;
    }

    /**
     * @param array $input
     * @return $this
     */
    public function setRnFilters(array $input)
    {
        $token = null;

        if (!empty($this->nnp_filter_json)) {
            $nnpFilter = json_decode($this->nnp_filter_json, true);

            if (!empty($nnpFilter['token'])) {
                $token = $nnpFilter['token'];
            }
        }

        $filters = [
            'token' => $token ? $token : bin2hex(openssl_random_pseudo_bytes(16)),
            'use_nnp_ported' => isset($input['rn_use_nnp_ported']) ? $input['rn_use_nnp_ported'] : '',
        ];

        if (isset($input['rn_country']) && count($input['rn_country'])) {
            $filters['country_code'] = $input['rn_country'];
            $filters['exclude_country'] = array_key_exists('rn_is_exclude_country', $input) ?
                $input['rn_is_exclude_country'] : '';
        }

        if (isset($input['rn_region']) && count($input['rn_region'])) {
            $filters['region_code'] = $input['rn_region'];
            $filters['exclude_region_code_fz'] = array_key_exists('rn_is_exclude_region', $input) ?
                $input['rn_is_exclude_region'] : '';
        }

        if (isset($input['rn_region_fz']) && count($input['rn_region_fz'])) {
            $filters['region_code_fz'] = $input['rn_region_fz'];
        }

        if (isset($input['rn_operator']) && count($input['rn_operator'])) {
            $filters['operator_id'] = $input['rn_operator'];
            $filters['exclude_operators'] = array_key_exists('rn_is_exclude_operators', $input) ?
                $input['rn_is_exclude_operators'] : '';
        }

        if (isset($input['rn_route_mnc']) && count($input['rn_route_mnc'])) {
            $filters['mnc'] = $input['rn_route_mnc'];
            $filters['exclude_mnc'] = array_key_exists('rn_is_exclude_mnc', $input) ?
                $input['rn_is_exclude_mnc'] : '';
        }

        $this->nnp_filter_json = Json::encode($filters);
        
        return $this;
    }

    public function setToken()
    {
        $token = null;
        
        if (!empty($this->nnp_filter_json)) {
            $nnpFilter = json_decode($this->nnp_filter_json, true);
        
            if (!empty($nnpFilter['token'])) {
                $token = $nnpFilter['token'];
            }
        }
        
        $filters = [
            'token' => $token ? $token : bin2hex(openssl_random_pseudo_bytes(16)),
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
        $nnpFilter = [];
        
        if (!empty($data['nnp_filter_json'])) {
            $nnpFilter = json_decode($data['nnp_filter_json'], true);
        }
        
        $data['manual_list'] = $this->getManualList();
        $data['smezhnost_list'] = $this->getSmezhnostList();
        $data['rossvyaz_operators'] = $this->getRossvyazOperators();
        $data['prefixes'] = $this->getPrefixlistPrefix()->count();
        $data['prefixes_buffer'] = $this->getPrefixlistPrefixPrepare()->count();
        $data['dt_update'] = $data['dt_update'] ? date('Y-m-d H:i:s', strtotime($data['dt_update'])) : '';
        $data['dt_prepare'] = $data['dt_prepare'] ? date('Y-m-d H:i:s', strtotime($data['dt_prepare'])) : '';
        $data['servers'] = isset($nnpFilter['servers']) ? $nnpFilter['servers'] : [];
        $data['pbx_list'] = isset($nnpFilter['pbx_list']) ? $nnpFilter['pbx_list'] : [];
        $data['trunk_roaming_list'] = isset($nnpFilter['trunk_list']) ? $nnpFilter['trunk_list'] : [];
        $data['fmc_trunk'] = isset($nnpFilter['fmc_trunk']) ? $nnpFilter['fmc_trunk'] : [];
        return $data;
    }

    /**
     * @return array
     */
    public function findUsagesInNumbers()
    {
        return
            (new Query)
                ->select([
                    'n.id', 'n.server_id', 'n.name',
                    'n.type_id', 'n.show_in_stat', 'n.sw_shared', 'n.object_comment'
                ])
                ->from(Number::tableName() . ' as n')
                ->innerJoin(Prefixlist::tableName() . ' as p', 'p.id = ANY (n.prefixlist_ids)')
                ->where('p.id = ' . $this->id)
                ->all();
    }
    
    /**
     * @return array
     */
    public function findUsagesInTrunkABRules()
    {
        return
            (new Query)
                ->select([
                    't.id', 't.trunk_name', 't.server_id', 't.object_comment'
                ])
                ->from(TrunkABfiltersRule::tableName() . ' as tab')
                ->innerJoin(Trunk::tableName() . ' as t', 't.id = tab.trunk_id')
                ->where('tab.prefixlist_id = ' . $this->id)
                ->all();
    }
}