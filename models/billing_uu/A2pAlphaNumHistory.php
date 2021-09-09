<?php

namespace app\models\billing_uu;

use app\exceptions\ModelValidationException;
use yii\db\Expression;
use app\queries\billing_uu\A2pAlphaNumHistoryQuery;
use Exception;
use Yii;

/**
 * @property int $id
 * @property int $pricelist_filter_a_id
 * @property int $pricelist_id
 * @property string $type
 * @property string $date_created
 * @property int $total_count
 * @property string $data_before
 */
class A2pAlphaNumHistory extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.a2p_alphanum_history';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['type', 'data_before', 'date_created'], 'string'],
            [['pricelist_filter_a_id', 'pricelist_id', 'total_count'], 'integer'],
        ];
    }

    public static function find()
    {
        return new A2pAlphaNumHistoryQuery(get_called_class());
    }
    
    /**
     * @param array|null $data
     * @return A2pAlphaNumHistory
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
    public function getAlphaNumList()
    {
        return $this->hasMany(A2pAlphaNum::className(), ['history_id' => 'id'])
            ->orderBy('billing_uu.a2p_alphanum.alphanum');
    }
    
    public static function createHistory($filterAId, $pricelistId, $numberCount, $type, $withData = true)
    {
        Yii::$app->db->createCommand(<<<SQL
update billing_uu.a2p_alphanum_history pph
set data_before = null
where pricelist_filter_a_id = :a_id
and id not in (
    select id from billing_uu.a2p_alphanum_history
    where pricelist_filter_a_id = :a_id
    order by date_created desc
    limit 4
);
SQL
)
                ->bindValue(':a_id', $filterAId)
                ->execute();
                
        if ($withData) {
            $dataBefore = A2pAlphaNum::find()
                ->where(['pricelist_filter_a_id' => $filterAId])
                ->asArray()
                ->all();
        } else {
            $dataBefore = [];
        }
            
        $historyData = [
            'pricelist_filter_a_id' => $filterAId,
            'pricelist_id' => $pricelistId,
            'type' => $type,
            'date_created' => date('Y-m-d H:i:s'),
            'total_count' => $numberCount,
            'data_before' => json_encode($dataBefore)
        ];
        
        $historyObject = self::create($historyData);

        if (!$historyObject->save()) {
            throw new ModelValidationException($historyObject);
        }
        
        return $historyObject;
    }
    
    public function fillDataBefore()
    {
        $dataBefore = A2pAlphaNum::find()
            ->where(['pricelist_filter_a_id' => $this->pricelist_filter_a_id])
            ->asArray()
            ->all();
        
        $this->data_before = json_encode($dataBefore);
    }
}