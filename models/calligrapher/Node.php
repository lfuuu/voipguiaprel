<?php

namespace app\models\calligrapher;

use app\classes\traits\ModelRules;
use yii\db\ActiveRecord;

/**
 * Модель для таблицы "calligrapher.node"
 *
 * @property int    $node_id
 * @property string $node_name_id
 * @property int    $node_type_id
 * @property int    $region_id
 * @property string $comment
 * @property string $ipaddress
 * @property string $address
 * @property int    $status
 * @property int    $node_status_id
 * @property int    $russian_district_id
 * @property int    $russian_subject_id
 * @property int    $russian_city_id
 * @property int    $graph_id
 */
class Node extends ActiveRecord
{
    use ModelRules;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'calligrapher.node';
    }

    /**
     * Правила валидации модели.
     *
     * @return array
     */
    private static function rulesStatic()
    {
        return [
            [['node_name_id', 'comment', 'address'], 'string'],
            [['node_type_id', 'region_id', 'status', 'node_status_id', 'russian_district_id', 'russian_subject_id', 'russian_city_id', 'graph_id'], 'integer'],
            [['ipaddress'], 'ip', 'ipv4' => true, 'ipv6' => false, 'skipOnEmpty' => true], // При необходимости можно добавить валидатор ip, например, 'ip'
        ];
    }

    /**
     * Создание новой записи модели.
     *
     * @param array|null $data Данные для загрузки в модель
     * @return Node
     */
    public static function create(array $data = null)
    {
        $node = new self();
        $node->load($data, '');
        return $node;
    }

    /**
     * Удаление текущей записи модели.
     *
     * @return int|false Количество удалённых строк или false при ошибке
     */
    public function deleteRecord()
    {
        return $this->delete();
    }

    /**
     * Получение записи по первичному ключу.
     *
     * @param int $id Идентификатор записи (node_id)
     * @return Node|null
     */
    public static function getNode($id)
    {
        return self::findOne($id);
    }
}
