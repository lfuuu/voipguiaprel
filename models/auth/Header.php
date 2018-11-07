<?php

namespace app\models\auth;
use app\queries\auth\HeaderQuery;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $value
 */
class Header extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.header';
    }

    public static function find()
    {
        return new HeaderQuery(get_called_class());
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
            [['name', 'description', 'value'], 'string'],
        ];
    }
}