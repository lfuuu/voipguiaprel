<?php

namespace app\models\smsc_cdr;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "smsc_cdr.smsc_cdr".
 *
 * @property int    $id
 * @property int    $server_id
 * @property string $call_id
 * @property string $hash
 * @property string $setup_time
 * @property string $proto
 * @property string $gt
 * @property string $a_number
 * @property string $b_number
 * @property bool   $direction
 * @property int    $retries
 * @property string $disconnect_cause
 * @property int    $imsi
 * @property int    $service_start_timestamp
 * @property int    $id_since_start
 * @property string $login
 */
class SmscCdr extends ActiveRecord
{
    public static function tableName()
    {
        return 'smsc_cdr.smsc_cdr';
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
            [['id', 'server_id', 'retries', 'imsi', 'service_start_timestamp', 'id_since_start'], 'integer'],
            [['direction'], 'boolean'],
            [['call_id', 'hash', 'proto', 'gt', 'a_number', 'b_number', 'disconnect_cause', 'login'], 'string'],
            [['setup_time'], 'safe'],
        ];
    }
}
