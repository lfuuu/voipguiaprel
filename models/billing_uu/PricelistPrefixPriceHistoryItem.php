<?php

namespace app\models\billing_uu;
use yii\db\Expression;
use app\queries\billing_uu\PricelistPrefixPriceHistoryItemQuery;

/**
 * @property int $id
 * @property int $pricelist_prefix_price_history_id
 * @property string $prefix_b
 * @property string $price_old
 * @property string $price_new
 * @property string $date_from
 * @property string $date_to
 * @property string $type
 */
class PricelistPrefixPriceHistoryItem extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.pricelist_prefix_price_history_item';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['date_from', 'date_to', 'type', 'prefix_b', 'price_old', 'price_new'], 'string'],
            [['pricelist_prefix_price_history_id'], 'integer'],
        ];
    }

    public static function find()
    {
        return new PricelistPrefixPriceHistoryItemQuery(get_called_class());
    }
    
    /**
     * @param array|null $data
     * @return PricelistPrefixPriceHistoryItem
     */
    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }
}