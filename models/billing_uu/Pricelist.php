<?php

namespace app\models\billing_uu;

use app\classes\traits\ModelRules;
use app\queries\billing_uu\PricelistQuery;
use yii\db\Expression;
use yii\db\Query;

/**
 * @property int $id
 * @property string $name
 * @property string $currency_id
 * @property bool $orig
 * @property string $date_created
 * @property string $date_start
 * @property string $date_end
 * @property int $pricelist_version
 * @property bool $is_global
 * @property bool $is_active
 * @property int $basic_pricelist_location_id
 * @property string $description
 * @property int $default_tarification_free_seconds
 * @property int $default_tarification_interval_seconds
 * @property int $default_tarification_min_paid_seconds
 * @property int $service_type_id
 * @property int $default_tarification_type
 * @property int $minimum_minutes
 * @property int $minimum_cost
 * @property string $minimum_margin
 * @property int $minimum_margin_type
 * @property string $num_c_nnp_filter
 */
class Pricelist extends \yii\db\ActiveRecord
{
    use ModelRules;
    
    public static function tableName()
    {
        return 'billing_uu.pricelist';
    }
    
    private static function rulesStatic()
    {
        return [
            [['name', 'currency_id', 'date_created', 'date_start', 'date_end', 'description','num_c_nnp_filter'], 'string'],
            [['pricelist_version', 'pricelist_group_id', 'type_id', 'basic_pricelist_location_id',
                'default_tarification_free_seconds', 'default_tarification_interval_seconds',
                'default_tarification_min_paid_seconds', 'service_type_id', 'default_tarification_type',
                'minimum_minutes', 'minimum_cost', 'minimum_margin_type'], 'integer'],
            [['orig', 'is_global', 'is_active'], 'boolean'],
            [['minimum_margin'], 'string']
        ];
    }

    public static function find()
    {
        return new PricelistQuery(get_called_class());
    }
    
    /**
     * @param array|null $data
     * @return ImsiPartner
     */
    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }
    
    public function isInCommercialUse()
    {
        $result = self::find()
            ->select([
                'is_in_use' => new Expression('case when atl.id is not null then true else false end')
            ])
            ->leftJoin('billing_uu.package_pricelist pp', 'pp.nnp_pricelist_id = billing_uu.pricelist.id')
            ->leftJoin('billing_uu.account_tariff_light atl', 'atl.id = pp.tariff_id and now() between atl.activate_from and atl.deactivate_from')
            ->where(['billing_uu.pricelist.id' => $this->id])
            ->asArray()
            ->one();

        return $result['is_in_use'];
    }
    
    public function importFromOldVersion($oldPricelistId)
    {
        return (new Query())->select(new Expression('billing_uu.transfer_pricelist(:old_pricelist_id,:new_pricelist_id)'))
            ->addParams([':old_pricelist_id' => $oldPricelistId, ':new_pricelist_id' => $this->id])
            ->one();
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocation()
    {
        return $this->hasMany(PricelistLocation::className(), ['pricelist_id' => 'id'])
            ->select([
                'billing_uu.pricelist_location.*',
                'mcc_string' => new Expression('(select string_agg(country, \', \') from nnp.mcc where nnp.mcc.mcc = ANY (billing_uu.pricelist_location.mcc::text[]))'),
                'mnc_string' => new Expression('array_to_string(mnc, \', \')'),
                'mnc_name_string' => new Expression('(select string_agg(network, \', \') from nnp.mnc where nnp.mnc.mnc = ANY (billing_uu.pricelist_location.mnc::text[]) and nnp.mnc.mcc = ANY (billing_uu.pricelist_location.mcc::text[]))'),
                'delta_price' => new Expression('round(billing_uu.pricelist_location.delta_price, 6)'),
                'sim_partner' => new Expression('(select string_agg(name, \', \') from billing_uu.sim_imsi_partner where billing_uu.sim_imsi_partner.id = ANY (billing_uu.pricelist_location.sim_partner))'),
                'sim_profile' => new Expression('(select string_agg(name, \', \') from billing_uu.sim_imsi_profile where billing_uu.sim_imsi_profile.id = ANY (billing_uu.pricelist_location.sim_profile))'),
            ])
            ->orderBy('mcc_string');
    }
}