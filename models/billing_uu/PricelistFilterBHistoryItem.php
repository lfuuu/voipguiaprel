<?php

namespace app\models\billing_uu;
use yii\db\Expression;
use app\queries\billing_uu\PricelistFilterBHistoryItemQuery;

/**
 * @property int $id
 * @property int $pricelist_filter_b_history_id
 * @property int $nnp_filter_id
 * @property string $description
 * @property string $date_from
 * @property string $date_to
 * @property string $type
 */
class PricelistFilterBHistoryItem extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.pricelist_filter_b_history_item';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['date_from', 'date_to', 'type', 'description'], 'string'],
            [['pricelist_filter_b_history_id', 'nnp_filter_id'], 'integer'],
        ];
    }

    public static function find()
    {
        return new PricelistFilterBHistoryItemQuery(get_called_class());
    }
    
    /**
     * @param array|null $data
     * @return PricelistFilterBHistoryItem
     */
    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }
}