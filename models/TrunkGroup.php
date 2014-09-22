<?php

namespace app\models;

use app\classes\ArrayToCsv;
use app\queries\TrunkGroupQuery;

/**
 * @property int $id
 * @property int $config_version_id
 * @property string $name
 * @property array $trunk_ids
 * @property array $trunk_numbers
 * @property
 */
class TrunkGroup extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.trunk_group';
    }

    public static function find()
    {
        return new TrunkGroupQuery(get_called_class());
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
            [['name'], 'required'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    public function getTrunks()
    {
        if ($this->trunk_ids == '{}') return [];

        $list = [];

        if ($this->trunk_ids && $this->trunk_ids != '{}') {
            foreach(str_getcsv( trim($this->trunk_ids, '{}') ) as $value) {
                $list[] = $value;
            }
        }

        return $list;
    }

    public function setTrunks(array $trunkIds)
    {
        $arrayToCsv = new ArrayToCsv(',');

        $this->trunk_ids = '{' . $arrayToCsv->convertLine($trunkIds) . '}';

        $trunkNumbers = $this->trunkIdsToNumbers($trunkIds);
        $this->trunk_numbers = '{' . $arrayToCsv->convertLine($trunkNumbers) . '}';

        return $this;
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = parent::toArray($fields, $expand, $recursive);
        $data['trunk_ids'] = $this->getTrunks();
        return $data;
    }

    private function trunkIdsToNumbers(array $trunkIds)
    {
        $trunkNumbers = [];
        foreach ($trunkIds as $trunkId) {
            $trunk = Trunk::findOne($trunkId);
            if ($trunk) {
                $trunkNumbers[] = $trunk->number;
            }
        }
        return $trunkNumbers;
    }
}