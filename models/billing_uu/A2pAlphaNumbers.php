<?php

namespace app\models\billing_uu;

use app\queries\billing_uu\A2pAlphaNumbersQuery;

/**
 * @property int $id
 * @property string $name
 */
class A2pAlphaNumbers extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.a2p_alphanum_list';
    }

       /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['alphanum'], 'string'],
        ];
    }

    public static function find()
    {
        return new A2pAlphaNumbersQuery(get_called_class());
    }
    
    /**
     * @param array|null $data
     */
    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }
    
}