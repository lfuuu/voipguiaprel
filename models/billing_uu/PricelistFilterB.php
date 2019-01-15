<?php

namespace app\models\billing_uu;
use app\queries\billing_uu\PricelistFilterBQuery;
use yii\db\Expression;

/**
 * @property int $id
 * @property int $pricelist_filter_a_id
 * @property int $nnp_filter
 * @property string $nnp_destination
 * @property string $nnp_country
 * @property string $nnp_operator
 * @property string $nnp_region
 * @property string $nnp_city
 * @property string $nnp_ndc_type
 * @property bool $mode_selected
 * @property float $interconnect_price
 * @property float $ported_num_price
 * @property int $tarification_free_seconds
 * @property int $tarification_interval_seconds
 * @property int $tarification_type
 * @property int $tarification_min_paid_seconds
 * @property string $time_start
 * @property string $time_end
 * @property bool $f_inv_nnp_destination
 * @property bool $f_inv_nnp_country
 * @property bool $f_inv_nnp_operator
 * @property bool $f_inv_nnp_region
 * @property bool $f_inv_nnp_city
 * @property bool $f_inv_nnp_ndc_type
 * @property string $description
 */
class PricelistFilterB extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.pricelist_filter_b';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['nnp_destination', 'nnp_country', 'nnp_operator', 'nnp_region', 'nnp_city', 'nnp_ndc_type',
                'interconnect_price', 'ported_num_price', 'time_start', 'time_end', 'description'], 'string'],
            [['pricelist_filter_a_id', 'tarification_free_seconds', 'tarification_interval_seconds',
                'tarification_type', 'tarification_min_paid_seconds', 'nnp_filter'], 'integer'],
            [['mode_selected', 'f_inv_nnp_destination', 'f_inv_nnp_country', 'f_inv_nnp_operator',
                'f_inv_nnp_region', 'f_inv_nnp_city', 'f_inv_nnp_ndc_type'], 'boolean']
        ];
    }

    public static function find()
    {
        return new PricelistFilterBQuery(get_called_class());
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
    public function getPrefixPriceBasic()
    {
        return $this->hasMany(PricelistPrefixPrice::className(), ['pricelist_filter_b_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrefixPrice()
    {
        return $this->hasMany(PricelistPrefixPrice::className(), ['pricelist_filter_b_id' => 'id'])
            ->select(['*', 'b_number_price' => new Expression('round(billing_uu.pricelist_prefix_price.b_number_price, 4)')])
            ->innerJoin('(select *, row_number() over (partition by pricelist_filter_b_id order by prefix_b) as rownum 
                from billing_uu.pricelist_prefix_price p
                ) p1', 'p1.id = billing_uu.pricelist_prefix_price.id')
            ->where('p1.rownum <= :limit')
            ->orderBy('billing_uu.pricelist_prefix_price.prefix_b')
            ->addParams([':limit' => PricelistPrefixPrice::PAGE_LIMIT]);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrefixPriceNoLimit()
    {
        return $this->hasMany(PricelistPrefixPrice::className(), ['pricelist_filter_b_id' => 'id'])
            ->select([
                'billing_uu.pricelist_prefix_price.*',
                'b_number_price' => new Expression('round(billing_uu.pricelist_prefix_price.b_number_price, 4)')
            ])
            ->orderBy('billing_uu.pricelist_prefix_price.prefix_b, billing_uu.pricelist_prefix_price.date_from');
    }
    
    public function getPrefixPriceCount()
    {
        return $this->hasMany(PricelistPrefixPrice::className(), ['pricelist_filter_b_id' => 'id'])
            ->select(['pricelist_filter_b_id', 'total_count' => new Expression('count(*)')])
            ->groupBy('pricelist_filter_b_id');
    }

}