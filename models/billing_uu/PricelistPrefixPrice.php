<?php

namespace app\models\billing_uu;
use yii\db\Expression;
use app\queries\billing_uu\PricelistPrefixPriceQuery;

/**
 * @property int $id
 * @property int $pricelist_filter_b_id
 * @property string $prefix_b
 * @property float $b_number_price
 * @property int $change_flag
 */
class PricelistPrefixPrice extends \yii\db\ActiveRecord
{
    const PAGE_LIMIT = 10;
    
    public static function tableName()
    {
        return 'billing_uu.pricelist_prefix_price';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['prefix_b', 'b_number_price', 'date_from', 'date_to'], 'string'],
            [['pricelist_filter_b_id', 'change_flag'], 'integer'],
        ];
    }

    public static function find()
    {
        return new PricelistPrefixPriceQuery(get_called_class());
    }
    
    public static function checkIfPrefixDateExists($prefixB, $filterBId, $dateFrom, $id = null)
    {
        $query = self::find()
            ->where(['prefix_b' => $prefixB, 'pricelist_filter_b_id' => $filterBId, 'date_from' => $dateFrom]);
        
        if ($id) {
            $query
                ->andWhere('id <> :id')
                ->addParams([':id' => $id]);
        }
        
        return $query->exists();
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

    public static function getGroupedByPrice($filterAId)
    {
        $result = [];

        $tempResult = self::find()
            ->alias('p')
            ->select('p.*')
            ->select([
                'b_number_price',
                'ids' => new Expression('string_agg(distinct b.id::text, \',\')'),
                'description' => new Expression('string_agg(distinct b.description, \', \')'),
                'date_from' => new Expression('min(p.date_from)'),
            ])
            ->innerJoin('billing_uu.pricelist_filter_b b', 'b.id = p.pricelist_filter_b_id')
            ->where(['b.pricelist_filter_a_id' => $filterAId])
            ->groupBy(['b_number_price'])
            ->asArray()
            ->all();

        foreach ($tempResult as $item) {
            $result[(string)floatval($item['b_number_price'])] = $item;
        }

        return $result;
    }
}