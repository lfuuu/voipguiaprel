<?php

namespace app\models\auth;
use app\queries\auth\CamelTrunkNumberPreprocessingQuery;

/**
 * @property int $id
 * @property int $camel_trunk_id
 * @property int $order
 * @property bool $src
 * @property int $noa
 * @property int $length
 * @property int $prefix
 * @property int $abc_mode
 * @property string $object_comment
 * @property int $mod_type
 * @property int $start_pos
 * @property int $end_pos
 * @property string $mod_value
 * @property string $regex
 * @property bool $acc
 * @property bool $auth
 * @property bool $avoid_mod
 * @property int $number_id
 */
class CamelTrunkNumberPreprocessing extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.camel_trunk_number_preprocessing';
    }

    public static function find()
    {
        return new CamelTrunkNumberPreprocessingQuery(get_called_class());
    }

    public static function deleteByTrunk(CamelTrunk $trunk)
    {
        return self::deleteAll(['camel_trunk_id' => $trunk->id]);
    }

    public static function create(CamelTrunk $trunk, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->camel_trunk_id = $trunk->id;
        return $item;
    }

    public function rules()
    {
        return [
            [['prefix', 'object_comment', 'mod_value', 'regex'], 'string'],
            [['camel_trunk_id', 'order', 'noa', 'length',
                'abc_mode', 'mod_type', 'start_pos', 'end_pos', 'number_id'], 'integer'],
            [['src', 'acc', 'auth', 'avoid_mod'], 'boolean']
        ];
    }
}