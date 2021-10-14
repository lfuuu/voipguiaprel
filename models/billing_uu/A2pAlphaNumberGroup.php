<?php

namespace app\models\billing_uu;

use app\queries\billing_uu\A2pAlphaNumberGroupQuery;

/**
 * @property int $id
 * @property string $group_name
 */
class A2pAlphaNumberGroup extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.a2p_alphanum_group';
    }

       /**
     * @return array
     */
    public function rules()
    {
        return [
            [['group_name'], 'string'],
        ];
    }

    public static function find()
    {
        return new A2pAlphaNumberGroupQuery(get_called_class());
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