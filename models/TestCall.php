<?php

namespace app\models;

use app\classes\ArrayToCsv;
use app\queries\NumberQuery;

/**
 * @property int    $id
 * @property int    $server_id
 * @property string $name
 * @property bool   $orig
 * @property string $src_trunk_name
 * @property string $dst_trunk_name
 * @property string $connect_time
 * @property int    $session_time
 * @property string $src_number
 * @property string $dst_number
 * @property string $redirect_number
 * @property int    $src_noa
 * @property int    $dst_noa
 * @property string $note

 * @property Server $server
 * @property
 */
class TestCall extends \yii\db\ActiveRecord
{
    const STATUS_A_NUMBER = 1;
    const STATUS_B_NUMBER = 2;

    public static function tableName()
    {
        return 'auth.test_call';
    }

    public static function create(Server $server, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->server_id = $server->id;
        return $item;
    }

    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 100],
            [['orig'], 'boolean'],
            [['connect_time', 'note'], 'string'],
            [['session_time'], 'integer'],
            [['src_number', 'dst_number', 'redirect_number'], 'string', 'max' => 32],
            [['src_noa','dst_noa'], 'integer'],
            [['src_trunk_name', 'dst_trunk_name'], 'string', 'max' => 32],
            [['is_autotest',], 'boolean'],
            [['correct_answer'], 'string', 'max' => 128],
            [['testgroup_id'], 'integer'],
        ];
    }


    public function getServer()
    {
        return $this->hasOne(Server::className(), ['id' => 'server_id']);
    }
}