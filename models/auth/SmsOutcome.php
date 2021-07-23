<?php

namespace app\models\auth;
use app\queries\auth\SmsOutcomeQuery;

/**
 * @property int $id
 * @property string $name
 * @property int $type_id
 * @property int $server_id
 * @property bool $f_use_arguments
 * @property string $arguments
 * @property bool $f_set_credit_limit
 * @property bool $f_check_b_number
 */
class SmsOutcome extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.a2psms_outcome';
    }

    public static function find()
    {
        return new SmsOutcomeQuery(get_called_class());
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
            [['name','route_name', 'object_comment'], 'string'],
            [['id','type_id', 'server_id'], 'integer'],
        ];
    }
}