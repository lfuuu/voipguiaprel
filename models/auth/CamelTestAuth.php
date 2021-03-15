<?php

namespace app\models\auth;
use app\queries\auth\CamelTestAuthQuery;

/**
 * @property int $id
 * @property int $server_id
 * @property int $camel_testgroup_id
 * @property string $name
 * @property string $camel_trunk_name
 * @property string $gt_number
 * @property string $a_number
 * @property string $b_number
 * @property string $c_number
 * @property bool $is_autotest
 * @property string $correct_answer
 * @property string $note
 * @property bool $with_debug_info
 * @property string $object_comment
 */
class CamelTestAuth extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.camel_test_auth';
    }

    public static function find()
    {
        return new CamelTestAuthQuery(get_called_class());
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
            [['name', 'camel_trunk_name', 'gt_number', 'a_number', 'b_number','c_number',
                'correct_answer', 'note', 'object_comment'], 'string'],
            [['server_id', 'camel_testgroup_id'], 'integer'],
            [['is_autotest', 'with_debug_info'], 'boolean']
        ];
    }
}