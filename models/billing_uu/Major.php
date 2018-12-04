<?php

namespace app\models\billing_uu;
use app\queries\billing_uu\MajorQuery;
use yii\helpers\Json;

/**
 * @property int $id
 * @property string $name
 * @property int $order
 * @property int $country_code
 * @property string $nnp_filter_json
 * @property int $major_group_id
 */
class Major extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.major';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'nnp_filter_json'], 'string'],
            [['order', 'country_code', 'major_group_id'], 'integer'],
        ];
    }

    public static function find()
    {
        return new MajorQuery(get_called_class());
    }
    
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
            'token' => $token ? $token : bin2hex(openssl_random_pseudo_bytes(16)),
        ];
        
        if (isset($input['nnp_operator']) && count($input['nnp_operator'])) {
            $filters['operator_id'] = $input['nnp_operator'];
            $filters['exclude_operators'] = array_key_exists('nnp_exclude_operator', $input) ?
                $input['nnp_exclude_operator'] :
                '';
        }
        
        if (isset($input['nnp_country']) && count($input['nnp_country'])) {
            $filters['country_code'] = $input['nnp_country'];
            $filters['exclude_country'] = array_key_exists('nnp_exclude_country', $input) ?
                $input['nnp_exclude_country'] :
                '';
        }
        
        if (isset($input['nnp_region']) && count($input['nnp_region'])) {
            $filters['region_id'] = $input['nnp_region'];
            $filters['exclude_region'] = array_key_exists('nnp_exclude_region', $input) ?
                $input['nnp_exclude_region'] :
                '';
        }
        
        if (isset($input['nnp_city']) && count($input['nnp_city'])) {
            $filters['city_id'] = $input['nnp_city'];
            $filters['exclude_city'] = array_key_exists('nnp_exclude_city', $input) ?
                $input['nnp_exclude_city'] :
                '';
        }
        
        if (isset($input['nnp_ndc_type']) && count($input['nnp_ndc_type'])) {
            $filters['ndc_type_id'] = $input['nnp_ndc_type'];
            $filters['exclude_ndc_type'] = array_key_exists('nnp_exclude_ndc_type', $input) ?
                $input['nnp_exclude_ndc_type'] :
                '';
        }
        
        if (isset($input['nnp_destination']) && count($input['nnp_destination'])) {
            $filters['nnp_destination_id'] = $input['nnp_destination'];
            $filters['exclude_destination'] = array_key_exists('nnp_exclude_destination', $input) ?
                $input['nnp_exclude_destination'] :
                '';
        }
        
        $this->nnp_filter_json = Json::encode($filters);
        return $this;
    }
    
    /**
     * @param array|null $data
     * @return ImsiPartner
     */
    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }
}