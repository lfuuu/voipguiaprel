<?php

namespace app\models\billing_uu;
use app\queries\billing_uu\PricelistFilterBQuery;

/**
 * @property int $id
 * @property int $pricelist_filter_a_id
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
                'interconnect_price', 'ported_num_price'], 'string'],
            [['pricelist_filter_a_id', 'tarification_free_seconds', 'tarification_interval_seconds',
                'tarification_type', 'tarification_min_paid_seconds'], 'integer'],
            [['mode_selected'], 'boolean']
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
    public function getPrefixPrice()
    {
        return $this->hasMany(PricelistPrefixPrice::className(), ['pricelist_filter_b_id' => 'id'])->orderBy('prefix_b');
    }

}