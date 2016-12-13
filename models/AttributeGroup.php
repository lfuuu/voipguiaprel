<?php

namespace app\models;

use app\classes\ArrayToCsv;
use app\queries\AttributeGroupQuery;

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
            [['name'], 'string', 'max' => 50]
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