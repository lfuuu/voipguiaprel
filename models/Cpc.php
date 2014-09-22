<?php

namespace app\models;
use app\queries\CpcQuery;

/**
 * @property int $id
 * @property int $config_version_id
 * @property string $name
 * @property string $value
 * @property
 */
class Cpc extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.cpc';
    }

    public static function find()
    {
        return new CpcQuery(get_called_class());
    }

    public static function create(ConfigVersion $version, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->config_version_id = $version->id;
        return $item;
    }

    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 50],
            [['value'], 'string', 'max' => 50],
        ];
    }
}