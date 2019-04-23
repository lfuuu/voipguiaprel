<?php

namespace app\models\billing_uu;
use app\queries\billing_uu\MajorGroupQuery;

/**
 * @property int $id
 * @property string $name
 * @property bool $use_for_a
 * @property bool $use_for_b
 */
class MajorGroup extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.major_group';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['use_for_a', 'use_for_b'], 'boolean']
        ];
    }

    public static function find()
    {
        return new MajorGroupQuery(get_called_class());
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