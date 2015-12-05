<?php

namespace app\models;

/**
 * @property int $id
 * @property int $server_id
 * @property string $name
 * @property
 */
class TrunkGroup extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.trunk_group';
    }

    public static function create(Server $server, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->server_id = $server->id;
        return $item;
    }

    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 50],
        ];
    }

    public function extraFields()
    {
        return ['trunks'];
    }

    public function getTrunks()
    {
        return $this->hasMany(TrunkGroupItem::className(), ['trunk_group_id' => 'id']);
    }
}