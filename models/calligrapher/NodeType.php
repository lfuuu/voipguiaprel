<?php

namespace app\models\calligrapher;

use app\classes\traits\ModelRules;
use yii\db\ActiveRecord;

/**
 * Модель для таблицы "calligrapher.node_type"
 *
 * @property int    $node_type_id
 * @property string $node_type
 * @property string $comment
 */
class NodeType extends ActiveRecord
{
    use ModelRules;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'calligrapher.node_type';
    }

    /**
     * Правила валидации.
     */
    private static function rulesStatic()
    {
        return [
            [['node_type', 'comment'], 'string'],
        ];
    }

    /**
     * Создание новой записи.
     */
    public static function create(array $data = null)
    {
        $type = new self();
        $type->load($data, '');
        return $type;
    }

    /**
     * Удаление записи.
     */
    public function deleteRecord()
    {
        return $this->delete();
    }

    /**
     * Получить объект NodeType по первичному ключу.
     *
     * @param int $id
     * @return NodeType|null
     */
    public static function getType($id)
    {
        return self::findOne($id);
    }
}
