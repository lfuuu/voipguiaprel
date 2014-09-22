<?php

namespace app\models;
use app\queries\OperatorQuery;

/**
 * @property int $id
 * @property int $config_version_id
 * @property int $code
 * @property string $name
 * @property bool $source_rule_default_allowed
 * @property bool $destination_rule_default_allowed
 * @property int $default_priority
 * @property string $openca
 * @property bool $auto_routing
 * @property
 */
class Operator extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.operator';
    }

    public static function find()
    {
        return new OperatorQuery(get_called_class());
    }

    public static function create(ConfigVersion $version, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->config_version_id = $version->id;
        $item->default_priority = 0;
        return $item;
    }

    public function rules()
    {
        return [
            [['code'], 'integer', 'min'=> 1, 'max' => 99],
            [['name'], 'string', 'max' => 50],
            [['openca'], 'string', 'max' => 20],
            [['default_priority'], 'integer', 'min'=> -10, 'max' => 10],
            [['auto_routing', 'source_rule_default_allowed', 'destination_rule_default_allowed'], 'boolean'],
        ];
    }

    public function extraFields()
    {
        return ['priorities', 'rules'];
    }

    public function getPriorities()
    {
        return $this->hasMany(OperatorPriority::className(), ['operator_id' => 'id'])->orderBy('order');
    }

    public function getRules()
    {
        return $this->hasMany(OperatorRule::className(), ['operator_id' => 'id'])->orderBy('order');
    }
}