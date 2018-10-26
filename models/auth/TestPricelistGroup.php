<?php

namespace app\models\auth;

/**
 * @property int $id
 * @property string $name
 */
class TestPricelistGroup extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.test_pricelist_group';
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
            [['name'], 'string']
        ];
    }
}