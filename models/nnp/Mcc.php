<?php

namespace app\models\nnp;
use app\queries\nnp\MccQuery;

/**
 * @property int $id
 * @property string $country
 * @property string $iso
 * @property integer $country_code
 */
class Mcc extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'nnp.mcc';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['country', 'iso'], 'string'],
            [['mcc', 'country_code'], 'integer']
        ];
    }

    public static function find()
    {
        return new MccQuery(get_called_class());
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