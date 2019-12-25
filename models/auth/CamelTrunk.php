<?php

namespace app\models\auth;
use app\queries\auth\CamelTrunkQuery;

/**
 * @property int $id
 * @property string $name
 * @property int $camel_route_table_id
 * @property int $server_id
 * @property int $prefixlist_id
 * @property string $trunk_mcn
 * @property string $trunk_tele2
 * @property bool $insert_cdr
 */
class CamelTrunk extends \yii\db\ActiveRecord
{
    public $_subitems = [
        'numberPreprocessing' => 'getNumberPreprocessing'
    ];

    public static function tableName()
    {
        return 'auth.camel_trunk';
    }

    public static function find()
    {
        return new CamelTrunkQuery(get_called_class());
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
            [['name', 'trunk_mcn', 'trunk_tele2'], 'string'],
            [['prefixlist_id', 'camel_route_table_id', 'server_id'], 'integer'],
            [['insert_cdr'], 'boolean']
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNumberPreprocessing()
    {
        return $this->hasMany(CamelTrunkNumberPreprocessing::className(), ['camel_trunk_id' => 'id'])->orderBy('order');
    }
}