<?php

namespace app\models\a2p_sms_raw;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "a2p_sms_raw.a2p_sms_raw".
 *
 * @property int    $id
 * @property bool   $orig
 * @property int    $server_id
 * @property int    $sms_call_id
 * @property int    $account_id
 * @property string $charge_time
 * @property string $src_number
 * @property string $dst_number
 * @property string $src_route
 * @property string $dst_route
 * @property float  $cost
 * @property float  $rate
 * @property int    $count
 * @property int    $account_tariff_light_id
 * @property int    $package_pricelist_id
 * @property int    $pricelist_location_id
 * @property int    $cdr_id
 * @property int    $location_id
 * @property string $mcc
 * @property string $mnc
 */
class A2pSmsRaw extends ActiveRecord
{
    public static function tableName()
    {
        return 'a2p_sms_raw.a2p_sms_raw';
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
            [['id', 'server_id', 'sms_call_id', 'account_id', 'account_tariff_light_id', 'package_pricelist_id', 'pricelist_location_id', 'cdr_id', 'location_id'], 'integer'],
            [['orig'], 'boolean'],
            [['charge_time'], 'safe'],
            [['src_number', 'dst_number', 'src_route', 'dst_route', 'mcc', 'mnc'], 'string'],
            [['cost', 'rate'], 'number'],
            [['count'], 'integer'],
        ];
    }
}
