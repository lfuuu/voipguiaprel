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
 * @property int    $ttl
 * @property string $note
 * @property string $cpc
 * @property bool   $with_debug_info
 * @property int    $router_version
 * @property string $headers
 * @property int    $redirect_noa
 * @property string $object_comment

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
            [['src_noa','dst_noa','redirect_noa', 'ttl', 'router_version'], 'integer'],
            [['trunk_name'], 'string', 'max' => 32],
            [['is_autotest', 'with_debug_info'], 'boolean'],
            [['correct_answer'], 'string', 'max' => 128],
            [['testgroup_id'], 'integer'],
            [['note', 'cpc', 'headers'], 'string'],
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
        ];
    }


    public function getServer()
    {
        return $this->hasOne(Server::className(), ['id' => 'server_id']);
    }
}