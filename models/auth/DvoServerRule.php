<?php
namespace app\models\auth;

/**
 * This is the model class for table "auth.dvo_server_rule".
 *
 * @property int $id
 * @property int $server_id
 * @property int $telemetry_reciever_id
 * @property int|null $number_id_filter_a
 * @property bool $allow
 * @property string|null $object_comment
 * @property int|null $order
 */
class DvoServerRule extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth.dvo_server_rule';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['server_id'], 'required'],
            [['server_id', 'number_id_filter_a', 'order', 'telemetry_reciever_id'], 'integer'],
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
            'allow' => 'Allow',
            'object_comment' => 'Object Comment',
            'order' => 'Order',
            'telemetry_reciever_id' => 'Telemetry Reciever ID',
        ];
    }

    /**
     * Creates a new DvoServerRule instance associated with a server.
     *
     * @param \app\models\Server $server
     * @param array|null $data
     * @return DvoServerRule
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
     * @param \app\models\Server $server
     * @return int The number of rows deleted.
     */
    public static function deleteByServer(\app\models\Server $server)
    {
        return self::deleteAll(['server_id' => $server->id]);
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
}
