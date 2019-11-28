<?php

namespace app\models\billing_api;
use app\queries\billing_api\ApiMethodQuery;

/**
 * @property int $id
 * @property int $api_id
 * @property string $method_sig
 * @property string $name
 * @property string $description
 */
class ApiMethod extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_api.api_method';
    }

    public static function find()
    {
        return new ApiMethodQuery(get_called_class());
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
            [['method_sig', 'name', 'description'], 'string'],
            [['api_id'], 'integer']
        ];
    }
}