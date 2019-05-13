<?php

namespace app\models;

use app\queries\AttributeQuery;

/**
 * @property int $id
 * @property string $name
 * @property string $note
 * @property string $object_comment
 */
class Attribute extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.attribute';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['note'], 'string'],
            [['name'], 'string', 'max' => 50],
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'Ключ',
            'name' => 'Название',
            'note' => 'Примечание',
        ];
    }

    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }

}