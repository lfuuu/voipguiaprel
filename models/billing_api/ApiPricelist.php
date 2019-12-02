<?php

namespace app\models\billing_api;
use app\queries\billing_api\ApiPricelistQuery;

/**
 * @property int $id
 * @property string $name
 */
class ApiPricelist extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_api.api_pricelist';
    }

    public static function find()
    {
        return new ApiPricelistQuery(get_called_class());
    }

    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }

    public function rules()
    {
        return [
            [['name'], 'string'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(ApiPricelistItem::className(), ['pricelist_id' => 'id']);
    }
}