<?php

namespace app\models\billing_uu;

use app\queries\billing_uu\A2pAlphaNumberListGroupQuery;

/**
 * @property int $id
 * @property int $alphanum_list_id
 * @property int $group_id
 */
class A2pAlphaNumberListGroup extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.a2p_alphanum_list_group';
    }

       /**
     * @return array
     */
    public function rules()
    {
        return [
            [['alphanum_list_id', 'group_id'], 'string'],
        ];
    }

    public static function find()
    {
        return new A2pAlphaNumberListGroupQuery(get_called_class());
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