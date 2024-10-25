<?php
namespace app\models\auth;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "auth.epvv_transit_rule".
 *
 * @property int $id
 * @property int $hub_id
 * @property int|null $number_id_filter_a
 * @property int|null $number_id_filter_b
 * @property int|null $number_id_filter_c
 * @property bool $is_orig
 * @property bool $allow
 * @property string|null $object_comment
 * @property int|null $order
 * @property int|null $trunk_group_id
 * @property int|null $ac_mode
 */
class EpvvTransitRule extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.epvv_transit_rule';
    }

    public function rules()
    {
        return [
            [['hub_id'], 'required'],
            [['hub_id', 'number_id_filter_a', 'number_id_filter_b', 'number_id_filter_c', 'order', 'trunk_group_id', 'ac_mode'], 'integer'],
            [['allow', 'is_orig'], 'boolean'],
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'hub_id' => 'Hub ID',
            'number_id_filter_a' => 'Number ID Filter A',
            'number_id_filter_b' => 'Number ID Filter B',
            'number_id_filter_c' => 'Number ID Filter C',
            'allow' => 'Allow',
            'object_comment' => 'Object Comment',
            'order' => 'Order',
            'trunk_group_id' => 'Trunk Group ID',
            'ac_mode' => 'AC Mode',
            'is_orig' => 'Is Origination',
        ];
    }
}
