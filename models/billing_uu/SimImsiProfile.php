<?php

namespace app\models\billing_uu;
use app\queries\billing_uu\SimImsiProfileQuery;

/**
 * @property int $id
 * @property int $partner_id
 * @property string $name
 * @property string $object_comment
 */
class SimImsiProfile extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.sim_imsi_profile';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name','object_comment'], 'string'],
            [['partner_id'], 'integer']
        ];
    }

    public static function find()
    {
        return new SimImsiProfileQuery(get_called_class());
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