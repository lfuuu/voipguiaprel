<?php

namespace app\models\billing_uu;
use app\queries\billing_uu\PricelistGroupQuery;

/**
 * @property int $id
 * @property string $name
 */
class PricelistGroup extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.pricelist_group';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string']
        ];
    }

    public static function find()
    {
        return new PricelistGroupQuery(get_called_class());
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