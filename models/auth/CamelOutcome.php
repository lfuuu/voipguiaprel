<?php

namespace app\models\auth;
use app\queries\auth\CamelOutcomeQuery;

/**
 * @property int $id
 * @property string $name
 * @property int $type_id
 * @property int $server_id
 */
class CamelOutcome extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.camel_outcome';
    }

    public static function find()
    {
        return new CamelOutcomeQuery(get_called_class());
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
            [['name'], 'string'],
            [['type_id', 'server_id'], 'integer']
        ];
    }
}