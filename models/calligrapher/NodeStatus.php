<?php

namespace app\models\calligrapher;

use app\classes\traits\ModelRules;
use yii\db\ActiveRecord;

/**
 * Модель для таблицы "calligrapher.node_status"
 *
 * @property int    $node_status_id
 * @property string $node_status
 * @property string $comment
 */
class NodeStatus extends ActiveRecord
{
    use ModelRules;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'calligrapher.node_status';
    }

    /**
     * Правила валидации.
     *
     * @return array
     */
    private static function rulesStatic()
    {
        return [
            [['node_status', 'comment'], 'string'],
        ];
    }

    /**
     * Создание новой записи.
     *
     * @param array|null $data
     * @return NodeStatus
     */
    public static function create(array $data = null)
    {
        $status = new self();
        $status->load($data, '');
        return $status;
    }

    /**
     * Удаление записи.
     *
     * @return int|false
     */
    public function deleteRecord()
    {
        return $this->delete();
    }

    /**
     * Получить объект NodeStatus по первичному ключу.
     *
     * @param int $id
     * @return NodeStatus|null
     */
    public static function getStatus($id)
    {
        return self::findOne($id);
    }
}
