<?php

namespace app\models\billing_uu;
use app\queries\billing_uu\PricelistFilterAQuery;

/**
 * @property int $id
 * @property int $pricelist_location_id
 * @property int $nnp_filter
 * @property string $nnp_destination
 * @property string $nnp_country
 * @property string $nnp_operator
 * @property string $nnp_region
 * @property string $nnp_city
 * @property string $nnp_ndc_type
 * @property string $time_start
 * @property string $time_end
 * @property bool $mode_selected
 * @property bool $f_inv_nnp_destination
 * @property bool $f_inv_nnp_country
 * @property bool $f_inv_nnp_operator
 * @property bool $f_inv_nnp_region
 * @property bool $f_inv_nnp_city
 * @property bool $f_inv_nnp_ndc_type
 * @property string $description
 */
class PricelistFilterA extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.pricelist_filter_a';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['nnp_destination', 'nnp_country', 'nnp_operator', 'nnp_region', 'nnp_city',
                'nnp_ndc_type', 'time_start', 'time_end', 'description'], 'string'],
            [['pricelist_location_id', 'nnp_filter'], 'integer'],
            [['mode_selected', 'f_inv_nnp_destination', 'f_inv_nnp_country', 'f_inv_nnp_operator',
                'f_inv_nnp_region', 'f_inv_nnp_city', 'f_inv_nnp_ndc_type'], 'boolean']
        ];
    }

    public static function find()
    {
        return new PricelistFilterAQuery(get_called_class());
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
    public function getFilterB()
    {
        return $this->hasMany(PricelistFilterB::className(), ['pricelist_filter_a_id' => 'id'])
            ->select(['billing_uu.pricelist_filter_b.*', 'date_trunc(\'second\', billing_uu.pricelist_filter_b.time_start) as time_start',
                'date_trunc(\'second\', billing_uu.pricelist_filter_b.time_end) as time_end',
                'c.nnp_country_name', 'd.nnp_destination_name', 'o.nnp_operator_name', 'r.nnp_region_name', 'cty.nnp_city_name', 't.nnp_ndc_type_name'])
            ->leftJoin('(select b.id, string_agg(name_rus, \', \') as nnp_country_name from nnp.country c join billing_uu.pricelist_filter_b b on c.code = any(b.nnp_country) group by b.id) as c', 'c.id = billing_uu.pricelist_filter_b.id')
            ->leftJoin('(select b.id, string_agg(name, \', \') as nnp_destination_name from nnp.destination d join billing_uu.pricelist_filter_b b on d.id = any(b.nnp_destination) group by b.id) as d', 'd.id = billing_uu.pricelist_filter_b.id')
            ->leftJoin('(select b.id, string_agg(name, \', \') as nnp_operator_name from nnp.operator o join billing_uu.pricelist_filter_b b on o.id = any(b.nnp_operator) group by b.id) as o', 'o.id = billing_uu.pricelist_filter_b.id')
            ->leftJoin('(select b.id, string_agg(name, \', \') as nnp_region_name from nnp.region r join billing_uu.pricelist_filter_b b on r.id = any(b.nnp_region) group by b.id) as r', 'r.id = billing_uu.pricelist_filter_b.id')
            ->leftJoin('(select b.id, string_agg(name, \', \') as nnp_city_name from nnp.city cty join billing_uu.pricelist_filter_b b on cty.id = any(b.nnp_city) group by b.id) as cty', 'cty.id = billing_uu.pricelist_filter_b.id')
            ->leftJoin('(select b.id, string_agg(name, \', \') as nnp_ndc_type_name from nnp.ndc_type t join billing_uu.pricelist_filter_b b on t.id = any(b.nnp_ndc_type) group by b.id) as t', 't.id = billing_uu.pricelist_filter_b.id')
            ->orderBy('id');
    }
}