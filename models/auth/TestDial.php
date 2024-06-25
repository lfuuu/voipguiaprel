<?php
namespace app\models\auth;

use app\models\Trunk;
use app\queries\auth\TestDialQuery;

/**
 * @property int $id
 * @property string $name
 * @property int $server_id
 * @property int $testgroup_id
 * @property string $term_trunk_id
 * @property string $src_number
 * @property string $dst_number
 * @property string $redirect_number
 * @property int $duration
 * @property string $note
 * @property bool $is_autotest
 * @property string $object_comment
 * @property string $autocall_uuid
 * @property string $nas_ip_address
 */
class TestDial extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.test_dial';
    }

    public static function find()
    {
        return new TestDialQuery(get_called_class());
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
            [['server_id', 'testgroup_id', 'duration', 'term_trunk_id'], 'integer'],
            [['name', 'src_number', 'dst_number', 'redirect_number', 'note', 'object_comment', 'autocall_uuid', 'nas_ip_address'], 'string'],
            [['is_autotest'], 'boolean']
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTermTrunk()
    {
        return $this->hasOne(Trunk::className(), ['id' => 'term_trunk_id']);
    }
}