<?php

namespace app\models\smsc_raw;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "smsc_raw.smsc_raw".
 *
 * @property int    $id
 * @property int    $server_id
 * @property int    $account_id
 * @property string $orig_gt
 * @property string $smpp_gt
 * @property string $term_gt
 * @property string $src_number
 * @property string $dst_number
 * @property string $src_imsi
 * @property string $dst_imsi
 * @property int    $orig_cdr_id
 * @property int    $smpp_cdr_id
 * @property int    $term_cdr_id
 * @property string $setup_time
 * @property int    $account_tariff_light_id
 * @property int    $package_pricelist_id
 * @property int    $pricelist_location_id
 * @property float  $cost
 * @property float  $rate
 * @property int    $count
 * @property int    $location_id
 * @property int    $service_start_timestamp
 * @property int    $id_since_start
 * @property int    $stats_package_sms_id
 * @property int    $package_sms_consumed
 */
class SmscRaw extends ActiveRecord
{
    public static function tableName()
    {
        return 'smsc_raw.smsc_raw';
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
            [['id', 'server_id', 'account_id', 'orig_cdr_id', 'smpp_cdr_id', 'term_cdr_id', 'account_tariff_light_id', 'package_pricelist_id', 'pricelist_location_id', 'count', 'location_id', 'service_start_timestamp', 'id_since_start', 'stats_package_sms_id', 'package_sms_consumed'], 'integer'],
            [['cost', 'rate'], 'number'],
            [['orig_gt', 'smpp_gt', 'term_gt', 'src_number', 'dst_number', 'src_imsi', 'dst_imsi'], 'string'],
            [['setup_time'], 'safe'],
        ];
    }
}
