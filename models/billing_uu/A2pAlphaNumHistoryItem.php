<?php

namespace app\models\billing_uu;
use yii\db\Expression;
use app\queries\billing_uu\A2pAlphaNumHistoryItemQuery;

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
class A2pAlphaNumHistoryItem extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.a2p_alphanum_history_item';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['type', 'alphanum'], 'string'],
            [['a2p_alphanum_history_id'], 'integer'],
        ];
    }

    public static function find()
    {
        return new A2pAlphaNumHistoryItemQuery(get_called_class());
    }
    
    /**
     * @param array|null $data
     * @return A2pAlphaNumHistoryItem
     */
    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }
}