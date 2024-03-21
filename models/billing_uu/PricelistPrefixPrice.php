<?php

namespace app\models\billing_uu;

use app\classes\traits\ModelRules;
use yii\db\Expression;
use app\queries\billing_uu\PricelistPrefixPriceQuery;

/**
 * @property int $id
 * @property int $pricelist_filter_b_id
 * @property string $prefix_b
 * @property float $b_number_price
 * @property int $change_flag
 * @property int $history_id
 * @property string $b_number_connect_price
 */
class PricelistPrefixPrice extends \yii\db\ActiveRecord
{
    use ModelRules;

    const PAGE_LIMIT = 10;
    
    const IMPORT_STATUS_INCREASE = 'increase';
    const IMPORT_STATUS_DECREASE = 'decrease';
    const IMPORT_STATUS_PROLONG = 'prolong';
    const IMPORT_STATUS_DELETE = 'delete';
    const IMPORT_STATUS_SKIPPED = 'skipped';

    public static function tableName()
    {
        return 'billing_uu.pricelist_prefix_price';
    }

    /**
     * @return array
     */
    private static function rulesStatic()
    {
        return [
            [['prefix_b', 'b_number_price', 'date_from', 'date_to', 'b_number_connect_price'], 'string'],
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
            [['pricelist_filter_b_id', 'change_flag', 'history_id'], 'integer'],
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
     * @return PricelistPrefixPrice
     */
    public static function create(array $data = null, $historyId = null)
    {
        $oldItems = self::find()
            ->where(['pricelist_filter_b_id' => $data['pricelist_filter_b_id'], 'prefix_b' => $data['prefix_b']])
            ->andWhere('date_to > :date_to')
            ->addParams(['date_to' => $data['date_from']])
            ->all();

        if (!empty($oldItems)) {
            foreach ($oldItems as $oldItem) {
                if ($historyId) {
                    $historyItem = [
                        'pricelist_prefix_price_history_id' => $historyId,
                        'prefix_b' => $data['prefix_b'],
                        'price_old' => $oldItem->b_number_price,
                        'price_new' => $data['b_number_price'],
                        'date_from' => $data['date_from'],
                        'date_to' => $data['date_to'],
                    ];

                    if ($oldItem->b_number_price < $data['b_number_price']) {
                        $historyItem['type'] = 'increase';
                    } elseif ($oldItem->b_number_price > $data['b_number_price']) {
                        $historyItem['type'] = 'decrease';
                    } elseif ($data['date_to'] == '3000-01-01') {
                        $historyItem['type'] = 'prolong';
                    } else {
                        $historyItem['type'] = 'delete';
                    }

                    $oldItem->history_id = $historyId;
                }

                $oldItem->date_to = $data['date_from'];
                $oldItem->save();
            }
        } else {
            if ($historyId) {
                $historyItem = [
                    'pricelist_prefix_price_history_id' => $historyId,
                    'prefix_b' => $data['prefix_b'],
                    'price_old' => '',
                    'price_new' => $data['b_number_price'],
                    'date_from' => $data['date_from'],
                    'date_to' => $data['date_to'],
                    'type' => 'new',
                ];
            }
        }

        $item = new self();

        $item->load($data, '');

        if ($historyId) {
            $item->history_id = $historyId;
            $historyItemObject = PricelistPrefixPriceHistoryItem::create($historyItem);
            $historyItemObject->save();
        }

        return $item;
    }

    public static function updateOldWithHistory(array $data, $historyId, $oldItems)
    {
        $newPrice = str_replace(',', '.', $data[2]);
        $newPrice = floatval($newPrice);

        if (!empty($oldItems)) {
            foreach ($oldItems as $oldItem) {
                if ($historyId) {
                    $historyItem = [
                        $historyId,
                        $data[1],
                        $oldItem['b_number_price'],
                        $newPrice,
                        $data[3],
                        $data[4]
                    ];

                    if ($oldItem['b_number_price'] < $newPrice) {
                        $historyItem[] = self::IMPORT_STATUS_INCREASE;
                    } elseif ($oldItem['b_number_price'] > $newPrice) {
                        $historyItem[] = self::IMPORT_STATUS_DECREASE;
                    } elseif ($data[4] == '3000-01-01') {
                        $historyItem[] = self::IMPORT_STATUS_PROLONG;
                    } elseif ($data[4] != $oldItem['date_to']) {
                        $historyItem[] = self::IMPORT_STATUS_DELETE;
                    } else {
                        // если старая цена = новая - повторение, следовательно показываем 'skipped' для дальнейшего отсеивания
                        // плюс учитываем дату окончания. Если она меняется, то отсеивать нельзя
                        $historyItem[] = self::IMPORT_STATUS_SKIPPED;
                    }
                }
            }
        } else {
            if ($historyId) {
                $historyItem = [
                    $historyId,
                    $data[1],
                    '',
                    $newPrice,
                    $data[3],
                    $data[4],
                    'new',
                ];
            }
        }

        return $historyItem;
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
            ->andWhere('p.date_to > now()')
            ->groupBy(['b_number_price'])
            ->asArray()
            ->all();

        foreach ($tempResult as $item) {
            $result[(string)floatval($item['b_number_price'])] = $item;
        }

        return $result;
    }
}
