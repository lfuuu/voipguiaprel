<?php

namespace app\models\calls_raw;
use app\models\billing\Client;
use app\models\billing\LegType;
use app\queries\calls_raw\CallsRawQuery;

/**
 *
 */
class CallsRaw extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'calls_raw.calls_raw';
    }

    public static function find()
    {
        return new CallsRawQuery(get_called_class());
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
            
        ];
    }
    
    public function getCurrency()
    {
        return $this->hasOne(Client::className(), ['id' => 'account_id'])->select(['id', 'currency']);
    }
    
    public function getLegTypeName()
    {
        return $this->hasOne(LegType::className(), ['id' => 'leg_type'])->select(['id', 'note']);
    }
}