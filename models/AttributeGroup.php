<?php

namespace app\models;

use app\classes\ArrayToCsv;
use app\queries\AttributeGroupQuery;

/**
 * @property int $id
 * @property string $name
 * @property string $note
 * @property string $attributeslist_ids
 * @property string $object_comment
 */
class AttributeGroup extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return 'auth.attribute_group';
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

    public function getAttributesLists()
    {
        if ($this->attributeslist_ids == '{}') return [];

        $list = [];

        if ($this->attributeslist_ids && $this->attributeslist_ids != '{}') {
            foreach (str_getcsv(trim($this->attributeslist_ids, '{}')) as $value) {
                $list[] = $value;
            }
        }

        return $list;
    }

    public function setAttributesLists(array $list)
    {
        $arrayToCsv = new ArrayToCsv(',');
        $this->attributeslist_ids = '{' . $arrayToCsv->convertLine($list) . '}';
        return $this;
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = parent::toArray($fields, $expand, $recursive);
        $data['attributeslist_ids'] = $this->getAttributeslists();
        return $data;
    }

}