<?php

namespace app\models\billing_uu;
use app\queries\ImsiPartnerQuery;

/**
 * @property int $id
 * @property string $name
 * @property int $mvno_region_id
 * @property int $location_id
 * @property bool $is_active
 * @property bool $is_append_rn
 * @property bool $is_append_gn
 * @property string $object_comment
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
            [['mvno_region_id', 'location_id'], 'integer'],
            [['is_active', 'is_append_rn', 'is_append_gn'], 'boolean'],
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
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