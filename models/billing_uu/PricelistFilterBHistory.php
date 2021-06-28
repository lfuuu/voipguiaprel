<?php

namespace app\models\billing_uu;
use yii\db\Expression;
use app\queries\billing_uu\PricelistFilterBHistoryQuery;
use Yii;

/**
 * @property int $id
 * @property int $pricelist_filter_a_id
 * @property int $pricelist_id
 * @property string $date_from
 * @property string $date_to
 * @property string $date_created
 * @property string $type
 * @property int $total_count
 * @property string $data_before
 */
class PricelistFilterBHistory extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.pricelist_filter_b_history';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['date_from', 'date_to', 'date_created', 'type', 'data_before'], 'string'],
            [['pricelist_filter_a_id', 'pricelist_id', 'total_count'], 'integer'],
        ];
    }

    public static function find()
    {
        return new PricelistFilterBHistoryQuery(get_called_class());
    }
    
    /**
     * @param array|null $data
     * @return PricelistFilterBHistory
     */
    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrefixPriceList()
    {
        return $this->hasMany(PricelistPrefixPrice::className(), ['history_id' => 'id'])
            ->orderBy('billing_uu.pricelist_prefix_price.prefix_b');
    }
    
    public static function createHistory($filterAId, $pricelistId, $dateStart, $dateEnd, $prefixCount, $type)
    {
        Yii::$app->db->createCommand(<<<SQL
update billing_uu.pricelist_filter_b_history fbh
set data_before = null
where pricelist_filter_a_id = :a_id
and id not in (
    select id from billing_uu.pricelist_filter_b_history
    where pricelist_filter_a_id = :a_id
    order by date_created desc
    limit 4
);
SQL
)
                ->bindValue(':a_id', $filterAId)
                ->execute();
                
                
        $dataBefore = [];
        $dataBefore = PricelistFilterB::find()
            ->where(['pricelist_filter_a_id' => $filterAId])
            ->asArray()
            ->all();
            
        $historyData = [
            'pricelist_filter_a_id' => $filterAId,
            'pricelist_id' => $pricelistId,
            'date_from' => $dateStart,
            'date_to' => $dateEnd,
            'date_created' => date('Y-m-d H:i:s'),
            'type' => $type,
            'total_count' => $prefixCount,
            'data_before' => json_encode($dataBefore)
        ];
        
        $historyObject = self::create($historyData);
        $historyObject->save();
        
        return $historyObject;
    }
}