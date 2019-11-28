<?php

namespace app\models\billing_api;
use app\queries\billing_api\ApiPricelistItemQuery;

/**
 * @property int $id
 * @property int $pricelist_id
 * @property int $api_id
 * @property int $api_method_id
 * @property bool $enabled
 * @property string $price
 */
class ApiPricelistItem extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_api.api_pricelist_item';
    }

    public static function find()
    {
        return new ApiPricelistItemQuery(get_called_class());
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
            [['pricelist_id', 'api_id', 'api_method_id'], 'integer'],
            [['price'], 'string'],
            [['enabled'], 'boolean'],
        ];
    }
}