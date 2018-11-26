<?php

namespace app\models\billing_uu;
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
 */
class Pricelist extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.pricelist';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'currency_id', 'date_created', 'date_start', 'date_end', 'description'], 'string'],
            [['pricelist_version', 'pricelist_group_id', 'type_id', 'basic_pricelist_location_id',
                'default_tarification_free_seconds', 'default_tarification_interval_seconds',
                'default_tarification_min_paid_seconds', 'service_type_id'], 'integer'],
            [['orig', 'is_global', 'is_active'], 'boolean']
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
            ->leftJoin('billing_uu.account_tariff_light atl', 'atl.id = pp.tariff_id')
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
                'mcc_string' => new Expression('(select string_agg(country, \', \') from nnp.mcc where nnp.mcc.mcc = ANY (billing_uu.pricelist_location.mcc))'),
                'mnc_string' => new Expression('array_to_string(mnc, \', \')'),
                'delta_price' => new Expression('round(billing_uu.pricelist_location.delta_price, 4)')
            ])
            ->orderBy('id');
    }
}