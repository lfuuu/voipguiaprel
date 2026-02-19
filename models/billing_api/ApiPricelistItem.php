<?php

namespace app\models\billing_api;

use app\queries\billing_api\ApiPricelistItemQuery;
use yii\db\ActiveQuery;

/**
 * @property int $id
 * @property int $pricelist_id
 * @property int $api_id
 * @property int $api_method_id
 * @property bool $enabled
 * @property string $price
 * @property string $cost
 *
 * @property Api $api
 * @property ApiMethod $apiMethod
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

    public static function create(array $data = null, ApiPricelist $pricelist = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->pricelist_id = $pricelist->id;
        return $item;
    }

    public function rules()
    {
        return [
            [['pricelist_id', 'api_id', 'api_method_id'], 'integer'],
            [['price', 'cost'], 'string'],
            [['enabled'], 'boolean'],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getApi(): ActiveQuery
    {
        return $this->hasOne(Api::class, ['id' => 'api_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getApiMethod(): ActiveQuery
    {
        return $this->hasOne(ApiMethod::class, ['id' => 'api_method_id']);
    }

    /**
     * @param ApiPricelist $pricelist
     * @return int
     */
    public static function deleteByPricelist(ApiPricelist $pricelist)
    {
        return self::deleteAll(['pricelist_id' => $pricelist->id]);
    }
}
