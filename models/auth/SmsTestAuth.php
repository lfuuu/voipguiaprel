<?php

namespace app\models\auth;
use app\queries\auth\SmsTestAuthQuery;

/**
 * @property int $id
 * @property int $server_id
 * @property int $a2p_testgroup_id
 * @property string $name
 * @property string $trunk_name
 * @property string $src_number
 * @property string $dst_number
 * @property bool $is_autotest
 * @property string $correct_answer
 * @property string $note
 * @property bool $with_debug_info
 * @property string $object_comment
 */
class SmsTestAuth extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.a2p_test_auth';
    }

    public static function find()
    {
        return new SmsTestAuthQuery(get_called_class());
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
            [['name', 'trunk_name', 'src_number', 'dst_number',
                'correct_answer', 'note', 'object_comment'], 'string'],
            [['server_id', 'a2p_testgroup_id','gate_id'], 'integer'],
            [['is_autotest', 'with_debug_info'], 'boolean']
        ];
    }
}