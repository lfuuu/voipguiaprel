<?php

namespace app\models\billing_uu;

use app\classes\traits\ModelRules;
use app\exceptions\ModelValidationException;
use Exception;
use Yii;

/**
 * @property int $id
 * @property int $pricelist_filter_a_id
 * @property string $alphanum
 * @property int $history_id
 */
class A2pAlphaNum extends \yii\db\ActiveRecord
{
    use ModelRules;

    const PAGE_LIMIT = 10;

    public static function tableName()
    {
        return 'billing_uu.a2p_alphanum';
    }

    /**
     * @return array
     */
    private static function rulesStatic()
    {
        return [
            [['alphanum'], 'string'],
            [['pricelist_filter_a_id','history_id'], 'integer'],
        ];
    }

    /**
     * @param array|null $data
     * @return A2pAlphaNum
     */
    public static function create(array $data = null, $historyId = null)
    {
        $transaction = A2pAlphaNum::getDb()->beginTransaction();
        try {
            $oldItems = self::find()
            ->where(['pricelist_filter_a_id' => $data['pricelist_filter_a_id'], 'alphanum' => $data['alphanum']])
            ->all();

            if (!empty($oldItems)) {
                foreach ($oldItems as $oldItem) {
                    if ($historyId) {
                        $historyItem = [
                            'a2p_alphanum_history_id' => $historyId,
                            'alphanum' => $data['alphanum'],
                        ];

                        $oldItem->history_id = $historyId;
                    }
                    if (!$oldItem->save()) {
                        throw new ModelValidationException($oldItem);
                    }
                }
            } else {
                if ($historyId) {
                    $historyItem = [
                        'a2p_alphanum_history_id' => $historyId,
                        'alphanum' => $data['alphanum'],
                        'type' => 'new',
                    ];
                }
            }

            $item = new self();

            $item->load($data, '');

            if ($historyId) {
                $item->history_id = $historyId;
                $historyItemObject = A2pAlphaNumHistoryItem::create($historyItem);
                if (!$historyItemObject->save()) {
                    throw new ModelValidationException($historyItemObject);
                }
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
        }
        

        return $item;
    }

    public static function updateOldWithHistory($data, $historyId, $oldItems)
    {

        if (!empty($oldItems)) {
            if ($historyId) {
                $historyItem = [
                    $historyId,
                    $data,
                ];
            }
        } else {
            if ($historyId) {
                $historyItem = [
                    $historyId,
                    $data,
                    'new',
                ];
            }
        }

        return $historyItem;
    }

}
