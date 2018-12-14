<?php

namespace app\models\billing_uu;
use app\models\nnp\Mcc;
use app\queries\billing_uu\PricelistLocationQuery;

/**
 * @property int $id
 * @property int $pricelist_id
 * @property int $location_id
 * @property string $mcc
 * @property string $mnc
 * @property string $description
 */
class PricelistLocation extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.pricelist_location';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['mcc', 'mnc', 'delta_price', 'description'], 'string'],
            [['pricelist_id','location_id'], 'integer'],
        ];
    }

    public static function find()
    {
        return new PricelistLocationQuery(get_called_class());
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
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFilterA()
    {
        return $this->hasMany(PricelistFilterA::className(), ['pricelist_location_id' => 'id'])
            ->select(['billing_uu.pricelist_filter_a.*', 'date_trunc(\'second\', billing_uu.pricelist_filter_a.time_start) as time_start',
                'date_trunc(\'second\', billing_uu.pricelist_filter_a.time_end) as time_end',
                'c.nnp_country_name', 'd.nnp_destination_name', 'o.nnp_operator_name', 'r.nnp_region_name', 'cty.nnp_city_name', 't.nnp_ndc_type_name'])
            ->leftJoin('(select b.id, string_agg(name_rus, \', \') as nnp_country_name from nnp.country c join billing_uu.pricelist_filter_a b on c.code = any(b.nnp_country) group by b.id) as c', 'c.id = billing_uu.pricelist_filter_a.id')
            ->leftJoin('(select b.id, string_agg(name, \', \') as nnp_destination_name from nnp.destination d join billing_uu.pricelist_filter_a b on d.id = any(b.nnp_destination) group by b.id) as d', 'd.id = billing_uu.pricelist_filter_a.id')
            ->leftJoin('(select b.id, string_agg(name, \', \') as nnp_operator_name from nnp.operator o join billing_uu.pricelist_filter_a b on o.id = any(b.nnp_operator) group by b.id) as o', 'o.id = billing_uu.pricelist_filter_a.id')
            ->leftJoin('(select b.id, string_agg(name, \', \') as nnp_region_name from nnp.region r join billing_uu.pricelist_filter_a b on r.id = any(b.nnp_region) group by b.id) as r', 'r.id = billing_uu.pricelist_filter_a.id')
            ->leftJoin('(select b.id, string_agg(name, \', \') as nnp_city_name from nnp.city cty join billing_uu.pricelist_filter_a b on cty.id = any(b.nnp_city) group by b.id) as cty', 'cty.id = billing_uu.pricelist_filter_a.id')
            ->leftJoin('(select b.id, string_agg(name, \', \') as nnp_ndc_type_name from nnp.ndc_type t join billing_uu.pricelist_filter_a b on t.id = any(b.nnp_ndc_type) group by b.id) as t', 't.id = billing_uu.pricelist_filter_a.id')
            ->orderBy('id');
    }
}