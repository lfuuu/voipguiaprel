<?php

namespace app\models;

use app\classes\ArrayToCsv;
use app\queries\NumberQuery;

/**
 * @property int    $id
 * @property int    $server_id
 * @property string $name
 * @property string $trunk_name
 * @property string $src_number
 * @property string $dst_number
 * @property string $redirect_number
 * @property int    $src_noa
 * @property int    $dst_noa
 * @property string $note
 * @property string $cpc

 * @property Server $server
 * @property
 */
class TestAuth extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.test_auth';
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
            [['src_number', 'dst_number', 'redirect_number'], 'string', 'max' => 32],
            [['src_noa','dst_noa'], 'integer'],
            [['trunk_name'], 'string', 'max' => 32],
            [['is_autotest',], 'boolean'],
            [['correct_answer'], 'string', 'max' => 128],
            [['testgroup_id'], 'integer'],
            [['note', 'cpc'], 'string'],
        ];
    }


    public function getServer()
    {
        return $this->hasOne(Server::className(), ['id' => 'server_id']);
    }
}