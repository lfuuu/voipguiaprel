<?php

namespace app\models\billing_uu;
use app\queries\billing_uu\PricelistPrefixPriceQuery;

/**
 * @property int $id
 * @property int $pricelist_filter_b_id
 * @property string $prefix_b
 * @property float $b_number_price
 * @property int $change_flag
 * @property int $parent_id
 */
class PricelistPrefixPrice extends \yii\db\ActiveRecord
{
    const PAGE_LIMIT = 10;
    
    public static function tableName()
    {
        return 'billing_uu.pricelist_prefix_price';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['prefix_b', 'b_number_price', 'date_from', 'date_to'], 'string'],
            [['pricelist_filter_b_id', 'change_flag', 'parent_id'], 'integer'],
        ];
    }

    public static function find()
    {
        return new PricelistPrefixPriceQuery(get_called_class());
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