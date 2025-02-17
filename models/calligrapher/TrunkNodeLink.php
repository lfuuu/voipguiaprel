<?php

namespace app\models\calligrapher;

use app\classes\traits\ModelRules;
use yii\db\ActiveRecord;

/**
 * Модель для таблицы "calligrapher.trunk_node_link"
 *
 * @property int    $trunk_node_link_id
 * @property int    $service_trunk_id
 * @property int    $node_id
 * @property bool   $orig
 * @property int    $contract_type_id
 * @property string $comment
 */
class TrunkNodeLink extends ActiveRecord
{
    use ModelRules;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'calligrapher.trunk_node_link';
    }

    /**
     * Правила валидации.
     */
    private static function rulesStatic()
    {
        return [
            [['service_trunk_id', 'node_id', 'contract_type_id'], 'integer'],
            [['orig'], 'boolean'],
            [['comment'], 'string'],
        ];
    }

    /**
     * Создание записи.
     */
    public static function create(array $data = null)
    {
        $link = new self();
        $link->load($data, '');
        return $link;
    }

    /**
     * Удаление записи.
     */
    public function deleteRecord()
    {
        return $this->delete();
    }

    /**
     * Получить объект по первичному ключу.
     */
    public static function getLink($id)
    {
        return self::findOne($id);
    }
}
