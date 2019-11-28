<?php

namespace app\models\billing_api;
use app\queries\billing_api\ApiQuery;

/**
 * @property int $id
 * @property int $server_id
 * @property string $api_sig
 * @property string $name
 * @property string $description
 */
class Api extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_api.api';
    }

    public static function find()
    {
        return new ApiQuery(get_called_class());
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
            [['api_sig', 'name', 'description'], 'string'],
            [['server_id'], 'integer']
        ];
    }
}