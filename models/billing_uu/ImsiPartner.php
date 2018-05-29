<?php

namespace app\models\billing_uu;
use app\queries\ImsiPartnerQuery;

/**
 * @property int $id
 * @property string $name
 * @property int $term_trunk_id
 * @property int $orig_trunk_id
 * @property bool $is_active
 */
class ImsiPartner extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.sim_imsi_partner';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['orig_trunk_id','term_trunk_id', 'mvno_region_id'], 'integer'],
            [['is_active'], 'boolean']
        ];
    }

    public static function find()
    {
        return new ImsiPartnerQuery(get_called_class());
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