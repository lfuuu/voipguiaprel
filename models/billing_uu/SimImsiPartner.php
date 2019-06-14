<?php

namespace app\models\billing_uu;
use app\queries\billing_uu\SimImsiPartnerQuery;
use yii\helpers\Json;

/**
 * @property int $id
 * @property string $name
 * @property int $term_trunk_id
 * @property int $orig_trunk_id
 * @property bool $is_active
 * @property int $mvno_region_id
 * @property bool $is_default
 * @property string $fake_lu
 * @property int $location_id
 * @property string $object_comment
 */
class SimImsiPartner extends \yii\db\ActiveRecord
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
            [['name', 'fake_lu', 'object_comment'], 'string'],
            [['is_active', 'is_default'], 'boolean'],
            [['term_trunk_id', 'orig_trunk_id', 'mvno_region_id', 'location_id'], 'integer']
        ];
    }

    public static function find()
    {
        return new SimImsiPartnerQuery(get_called_class());
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