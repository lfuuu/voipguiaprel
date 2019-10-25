<?php

namespace app\models\auth;
use app\queries\auth\CamelGtQuery;

/**
 * @property int $id
 * @property string $gt
 * @property string $oper
 * @property string $country
 * @property string $area
 * @property bool $loc
 * @property int $country_code
 * @property int $region_id
 * @property int $operator_id
 */
class CamelGt extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.camel_gt';
    }

    public static function find()
    {
        return new CamelGtQuery(get_called_class());
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
            [['gt', 'oper', 'country', 'area'], 'string'],
            [['loc'], 'boolean'],
            [['country_code', 'region_id', 'operator_id'], 'integer']
        ];
    }
}