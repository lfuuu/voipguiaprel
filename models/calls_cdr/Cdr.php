<?php

namespace app\models\calls_cdr;
use app\models\calls_raw\CallsRaw;
use app\queries\calls_cdr\CdrQuery;

/**
 * @property int $server_id
 * @property int $id
 * @property int $call_id
 * @property int $session_time
 * @property int $disconnect_cause
 * @property int $src_noa
 * @property int $dst_noa
 * @property int $hub_id
 * @property string $nas_ip
 * @property string $src_number
 * @property string $dst_number
 * @property string $redirect_number
 * @property string $setup_time
 * @property string $connect_time
 * @property string $disconnect_time
 * @property string $src_route
 * @property string $dst_route
 * @property string $hash
 * @property string $dst_replace
 * @property string $call_finished
 * @property string $releasing_party
 * @property string $in_sig_call_id
 * @property string $out_sig_call_id
 * @property string $session_time_precise
 * @property string $mcn_callid
 * @property bool $src_mgmn
 * @property bool $dst_mgmn
 * @property string $out_redirect_number
 */
class Cdr extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'calls_cdr.cdr';
    }

    public static function find()
    {
        return new CdrQuery(get_called_class());
    }

    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }
    
    public function getCallsRaw()
    {
        return $this->hasMany(CallsRaw::className(), ['mcn_callid' => 'mcn_callid', 'cdr_id' => 'id']);
    }

    public function rules()
    {
        return [
            [['server_id', 'id', 'call_id', 'session_time', 'disconnect_cause', 'src_noa', 'dst_noa', 'hub_id'], 'integer'],
            [['nas_ip', 'src_number', 'dst_number', 'redirect_number', 'setup_time', 'connect_time', 'disconnect_time',
                'src_route', 'dst_route', 'hash', 'dst_replace', 'call_finished', 'releasing_party', 'in_sig_call_id',
                'out_sig_call_id', 'session_time_precise', 'mcn_callid', 'out_redirect_number'], 'string'],
            [['src_mgmn', 'dst_mgmn'], 'boolean'],
        ];
    }
}