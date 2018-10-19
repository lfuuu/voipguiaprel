<?php

namespace app\models\nnp;
use app\queries\nnp\MncQuery;

/**
 * @property int $id
 * @property string $network
 * @property integer $mcc
 */
class Mnc extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'nnp.mnc';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['network'], 'string'],
            [['mnc', 'mcc'], 'integer']
        ];
    }

    public static function find()
    {
        return new MncQuery(get_called_class());
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
}