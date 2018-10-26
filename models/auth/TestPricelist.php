<?php

namespace app\models\auth;
use app\queries\auth\TestPricelistQuery;

/**
 * @property int $id
 * @property string $name
 * @property string $mcc
 * @property string $mnc
 * @property int $location_id
 * @property string $a_number
 * @property string $b_number
 * @property int $pricelist_id
 * @property boolean $is_orig
 * @property string $expected_result
 * @property int $test_pricelist_group_id
 */
class TestPricelist extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.test_pricelist';
    }

    public static function find()
    {
        return new TestPricelistQuery(get_called_class());
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
            [['location_id', 'pricelist_id', 'mcc', 'mnc', 'test_pricelist_group_id'], 'integer'],
            [['name', 'a_number', 'b_number', 'expected_price'], 'string'],
            [['is_orig'], 'boolean']
        ];
    }
}