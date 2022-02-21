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
 * @property int $location_id
 * @property bool $is_route_incoming_calls
 */
class CamelTrunk extends \yii\db\ActiveRecord
{
    public $_subitems = [
        'numberPreprocessing' => 'getNumberPreprocessing',
        'camelGtRules' => 'getCamelGtRules' 
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
            [['prefixlist_id', 'camel_route_table_id', 'server_id', 'location_id'], 'integer'],
            [['insert_cdr', 'is_route_incoming_calls', 'no_copy_numc_to_numa'], 'boolean']
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNumberPreprocessing()
    {
        return $this->hasMany(CamelTrunkNumberPreprocessing::className(), ['camel_trunk_id' => 'id'])->orderBy('order');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCamelGtRules()
    {
        return $this->hasMany(CamelGtRule::className(), ['camel_trunk_id' => 'id'])->orderBy('order');
    }
}