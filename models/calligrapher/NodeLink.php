<?php

namespace app\models\calligrapher;

use yii\db\ActiveRecord;
use app\classes\traits\ModelRules;

/**
 * Модель для таблицы "calligrapher.node_link"
 *
 * @property int $node_link_id
 * @property int $src_node_id
 * @property int $dst_node_id
 * @property bool $unidirect
 * @property int $weight
 * @property string $comment
 * @property string $src_trunk_name
 * @property string $dst_trunk_name
 * @property string $trunk_name
 */
class NodeLink extends ActiveRecord
{
    use ModelRules;

    public static function tableName()
    {
        return 'calligrapher.node_link';
    }

    private static function rulesStatic()
    {
        return [
            [['src_node_id', 'dst_node_id', 'weight'], 'integer'],
            [['unidirect'], 'boolean'],
            [['comment', 'src_trunk_name', 'dst_trunk_name', 'trunk_name'], 'string'],
            [['src_node_id', 'dst_node_id'], 'required'],
        ];
    }

    public static function create(array $data = null)
    {
        $link = new self();
        $link->load($data, '');
        return $link;
    }

    public function deleteRecord()
    {
        return $this->delete();
    }
}
