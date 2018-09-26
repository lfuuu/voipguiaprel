<?php

namespace app\models\auth;
use app\models\Server;
use app\queries\auth\RouteReplaceQuery;

/**
 * @property int $id
 * @property int $server_id
 * @property int $order
 * @property int $orig_trunk_id
 * @property int $orig_trunk_group_id
 * @property string $orig_attr
 * @property int $term_trunk_id
 * @property int $term_trunk_group_id
 * @property string $term_attr
 * @property string $action_type
 * @property int $replace_trunk_id
 */
class RouteReplace extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.route_replace';
    }

    public static function find()
    {
        return new RouteReplaceQuery(get_called_class());
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
            [['server_id', 'order', 'orig_trunk_id', 'orig_trunk_group_id',
                'term_trunk_id', 'term_trunk_group_id', 'replace_trunk_id'], 'integer'],
            [['orig_attr', 'term_attr', 'action_type'], 'string'],
        ];
    }
}