<?php
namespace app\models\auth;

/**
 * This is the model class for table "auth.corm_server_rule".
 *
 * @property int $id
 * @property int $server_id
 * @property int|null $number_id_filter_a
 * @property int|null $number_id_filter_b
 * @property int|null $number_id_filter_c
 * @property int|null telemetry_receiver_id
 * @property bool $allow
 * @property string|null $object_comment
 * @property int|null $order
 * @property int|null $trunk_group_id
 * @property bool $ac_mode
 */
class CormServerRule extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth.corm_server_rule';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['server_id'], 'required'],
            [['server_id', 'number_id_filter_a', 'number_id_filter_b', 'number_id_filter_c', 'telemetry_receiver_id', 'order', 'trunk_group_id', 'ac_mode'], 'integer'],
            [['allow'], 'boolean'],
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'server_id' => 'Server ID',
            'number_id_filter_a' => 'Number ID Filter A',
            'number_id_filter_b' => 'Number ID Filter B',
            'number_id_filter_c' => 'Number ID Filter C',
            'allow' => 'Allow',
            'object_comment' => 'Object Comment',
            'order' => 'Order',
            'trunk_group_id' => 'Trunk Group ID',
            'ac_mode' => 'AC Mode',
        ];
    }

    /**
     * Creates a new CormServerRule instance associated with a server.
     *
     * @param Server $server
     * @param array|null $data
     * @return CormServerRule
     */
    public static function create(\app\models\Server $server, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->server_id = $server->id;
        return $item;
    }

    /**
     * Deletes all rules associated with the given server.
     *
     * @param Server $server
     * @return int The number of rows deleted.
     */
    public static function deleteByServer(\app\models\Server $server)
    {
        return self::deleteAll(['server_id' => $server->id]);
    }

    /**
     * Gets the associated TrunkGroup.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTrunkGroup()
    {
        return $this->hasOne(TrunkGroup::className(), ['id' => 'trunk_group_id']);
    }

    /**
     * Gets the associated Number for filter A.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNumberA()
    {
        return $this->hasOne(Number::className(), ['id' => 'number_id_filter_a']);
    }

    /**
     * Gets the associated Number for filter B.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNumberB()
    {
        return $this->hasOne(Number::className(), ['id' => 'number_id_filter_b']);
    }

    /**
     * Gets the associated Number for filter C.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNumberC()
    {
        return $this->hasOne(Number::className(), ['id' => 'number_id_filter_c']);
    }
}
