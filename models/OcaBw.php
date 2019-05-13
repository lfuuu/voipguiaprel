<?php

namespace app\models;
use app\queries\OcaBwQuery;

/**
 * @property int $id
 * @property string $name
 * @property string $add_method_name
 * @property string $delete_method_name
 * @property string $read_method_name
 * @property string $prefixlist
 * @property integer $server_id
 * @property string $object_comment
 * @property
 */
class OcaBw extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.ss_autolist';
    }

    public static function find()
    {
        return new OcaBwQuery(get_called_class());
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
            [['name', 'add_method_name', 'delete_method_name', 'read_method_name', 'prefixlist'], 'string'],
            [['server_id'], 'integer'],
            [['is_global'], 'boolean'],
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
        ];
    }
}