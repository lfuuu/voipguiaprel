<?php

namespace app\models\billing_uu;
use yii\db\Expression;
use app\queries\billing_uu\PricelistPrefixPriceHistoryQuery;

/**
 * @property int $id
 * @property int $pricelist_filter_b_id
 * @property int $pricelist_id
 * @property string $date_from
 * @property string $date_to
 * @property string $date_created
 * @property string $type
 * @property int $total_count
 * @property string $data_before
 */
class PricelistPrefixPriceHistory extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.pricelist_prefix_price_history';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['date_from', 'date_to', 'date_created', 'type', 'data_before'], 'string'],
            [['pricelist_filter_b_id', 'pricelist_id', 'total_count'], 'integer'],
        ];
    }

    public static function find()
    {
        return new PricelistPrefixPriceHistoryQuery(get_called_class());
    }
    
    /**
     * @param array|null $data
     * @return PricelistPrefixPriceHistory
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
    public function getPrefixPriceList()
    {
        return $this->hasMany(PricelistPrefixPrice::className(), ['history_id' => 'id'])
            ->orderBy('billing_uu.pricelist_prefix_price.prefix_b');
    }
}