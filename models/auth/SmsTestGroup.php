<?php

namespace app\models\auth;
use app\queries\auth\SmsTestGroupQuery;

/**
 * @property int $id
 * @property string $group_name
 */
class SmsTestGroup extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.a2p_testgroup';
    }

    public static function find()
    {
        return new SmsTestGroupQuery(get_called_class());
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