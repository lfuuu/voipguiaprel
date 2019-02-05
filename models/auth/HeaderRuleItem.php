<?php

namespace app\models\auth;
use app\queries\auth\HeaderRuleItemQuery;

/**
 * @property int $id
 * @property int $order
 * @property int $header_rule_id
 * @property int $header_id
 * @property int $mode
 * @property string $value
 */
class HeaderRuleItem extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.header_rule_item';
    }

    public static function find()
    {
        return new HeaderRuleItemQuery(get_called_class());
    }

    public static function create(HeaderRule $headerRule, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->header_rule_id = $headerRule->id;
        return $item;
    }

    public function rules()
    {
        return [
            [['value'], 'string'],
            [['order', 'header_rule_id', 'header_id', 'mode'], 'integer'],
        ];
    }
    
    public static function deleteByHeaderRule(HeaderRule $headerRule)
    {
        return self::deleteAll(['header_rule_id' => $headerRule->id]);
    }
}