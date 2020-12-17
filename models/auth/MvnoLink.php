<?php

namespace app\models\auth;
use app\queries\auth\MvnoLinkQuery;

/**
 * @property int $id
 * @property int $server_id
 * @property int $mvno_partner_id
 * @property string $mvno_trunk_ids
 * @property string $trunk_groups
 * @property string $number_capacity
 * @property string $routing_number
 * @property string $ported_number_prefixes
 * @property string $excluded_number_prefixes
 */
class MvnoLink extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.mvno_link';
    }

    public static function find()
    {
        return new MvnoLinkQuery(get_called_class());
    }

    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }

    public function rules()
    {
        return [
            [['mvno_trunk_ids', 'trunk_groups', 'number_capacity', 'routing_number',
                'ported_number_prefixes', 'excluded_number_prefixes'], 'string'],
            [['server_id', 'mvno_partner_id'], 'integer'],
        ];
    }
}
