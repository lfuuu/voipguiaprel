<?php

namespace app\models\auth;
use app\queries\auth\CamelTestGroupQuery;

/**
 * @property int $id
 * @property string $group_name
 */
class CamelTestGroup extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.camel_testgroup';
    }

    public static function find()
    {
        return new CamelTestGroupQuery(get_called_class());
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
            [['group_name'], 'string'],
        ];
    }
}