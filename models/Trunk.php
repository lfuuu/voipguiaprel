<?php
namespace app\models;
use app\queries\TrunkQuery;

/**
 * @property int $id
 * @property int $config_version_id
 * @property string $name
 * @property int $number
 * @property bool $full_export
 * @property int $cpc_id
 * @property int $route_table_id
 * @property
 */
class Trunk extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.trunk';
    }

    public static function find()
    {
        return new TrunkQuery(get_called_class());
    }

    public static function create(ConfigVersion $version, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->config_version_id = $version->id;
        return $item;
    }

    public function rules()
    {
        return [
            [['name', 'number', 'cpc_id', 'route_table_id'], 'required'],
            [['name'], 'string', 'min'=>10,'max' => 50],
            [['number', 'cpc_id', 'route_table_id'], 'integer'],
            [['full_export'], 'boolean'],
        ];
    }

    public function extraFields()
    {
        return ['routeTable', 'cpc'];
    }

    public function getCpc()
    {
        return $this->hasOne(Cpc::className(), ['id' => 'cpc_id']);
    }

    public function getRouteTable()
    {
        return $this->hasOne(RouteTable::className(), ['id' => 'route_table_id']);
    }

}