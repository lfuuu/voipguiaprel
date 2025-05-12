<?php

namespace app\models\calls_detail;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "calls_detail.report_call".
 *
 * @property int         $id
 * @property string      $mcn_callid
 * @property int         $node_id
 * @property string      $dt_create
 * @property string|null $dt_update
 * @property int|null    $update_cnt
 * @property string      $call_id
 * @property string|null $connect_time
 * @property int         $duration
 * @property int         $disconnect_cause
 * @property int         $call_type_id
 * @property int|null    $supplement_service_id
 * @property string|null $caller_ip
 * @property int         $source_type
 * @property string      $caller_number
 * @property string|null $caller_internal_number
 * @property string|null $called_ip
 * @property int         $dest_type
 * @property string      $called_dialed_number
 * @property string      $called_number
 * @property string|null $called_internal_number
 * @property string|null $forwarded_number
 * @property string      $switch_id
 * @property string|null $edge_switch_id
 * @property int         $incoming_trunk_id
 * @property int         $outgoing_trunk_id
 * @property string|null $call_recording_id
 * @property string|null $caller_login
 * @property string|null $called_login
 * @property string|null $phone_card_number
 * @property string|null $sms_content
 * @property string|null $ss7_originating
 * @property string|null $ss7_destination
 * @property string|null $csv_formated
 * @property string|null $incoming_trunk_debug
 * @property string|null $outgoing_trunk_debug
 * @property string|null $switch_name_id_debug
 * @property string|null $f_error
 * @property string|null $f_warning
 */
class ReportCall extends ActiveRecord
{
    public static function tableName()
    {
        return 'calls_detail.report_call';
    }

    public function rules()
    {
        return [
            [['mcn_callid', 'call_id', 'duration', 'disconnect_cause', 'call_type_id', 'source_type', 'caller_number', 'dest_type', 'called_dialed_number', 'called_number', 'switch_id', 'incoming_trunk_id', 'outgoing_trunk_id'], 'required'],
            [['node_id', 'update_cnt', 'duration', 'disconnect_cause', 'call_type_id', 'supplement_service_id', 'source_type', 'dest_type', 'incoming_trunk_id', 'outgoing_trunk_id'], 'integer'],
            [['dt_create', 'dt_update', 'connect_time'], 'safe'],
            [['mcn_callid', 'call_id', 'caller_ip', 'caller_number', 'caller_internal_number', 'called_ip', 'called_dialed_number', 'called_number', 'called_internal_number', 'forwarded_number', 'switch_id', 'edge_switch_id', 'call_recording_id', 'caller_login', 'called_login', 'phone_card_number', 'sms_content', 'ss7_originating', 'ss7_destination', 'csv_formated', 'incoming_trunk_debug', 'outgoing_trunk_debug', 'switch_name_id_debug', 'f_error', 'f_warning'], 'string'],
        ];
    }
}
