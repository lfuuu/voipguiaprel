<?php

namespace app\models\auth;
use app\queries\auth\HeaderRuleQuery;

/**
 * @property int $id
 * @property int $server_id
 * @property string $name
 * @property string $description
 * @property boolean $sw_shared
 */
class HeaderRule extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.header_rule';
    }

    public static function find()
    {
        return new HeaderRuleQuery(get_called_class());
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
            [['name', 'description'], 'string'],
            [['server_id'], 'integer'],
            [['sw_shared'], 'boolean'],
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(HeaderRuleItem::className(), ['header_rule_id' => 'id'])->orderBy('order');
    }
}