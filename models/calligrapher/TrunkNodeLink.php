<?php

namespace app\models\calligrapher;

use yii\db\ActiveRecord;
use app\classes\traits\ModelRules;

/**
 * Модель для таблицы "calligrapher.trunk_node_link"
 *
 * @property int         $trunk_node_link_id
 * @property int|null    $service_trunk_id
 * @property int|null    $node_id
 * @property bool|null   $orig
 * @property int|null    $contract_type_id
 * @property string|null $comment
 * @property string|null $start_date       // TIMESTAMP без часового пояса
 * @property string|null $end_date         // TIMESTAMP без часового пояса
 * @property int|null    $id_phys_trunk
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
     * Правила валидации модели.
     *
     * @return array
     */
    public function rules()
    {
        return self::rulesStatic();
    }

    /**
     * Статические правила валидации.
     *
     * @return array
     */
    private static function rulesStatic()
    {
        return [
            // обязательные поля
            [['service_trunk_id', 'node_id'], 'required'],

            // целочисленные поля
            [['service_trunk_id', 'node_id', 'contract_type_id', 'id_phys_trunk'], 'integer'],

            // булево поле
            [['orig'], 'boolean'],

            // текстовое поле
            [['comment'], 'string'],

            // даты — безопасны для массовой загрузки
            [['start_date', 'end_date'], 'safe'],
        ];
    }

    /**
     * Перед сохранением: преобразуем пустые строки в null для nullable-полей.
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->comment !== null && trim($this->comment) === '') {
            $this->comment = null;
        }

        return true;
    }

    /**
     * Создание новой записи модели.
     *
     * @param array|null $data
     * @return TrunkNodeLink
     */
    public static function create(array $data = null)
    {
        $link = new self();
        $link->load($data, '');
        return $link;
    }

    /**
     * Удаление текущей записи модели.
     *
     * @return int|false
     */
    public function deleteRecord()
    {
        return $this->delete();
    }

    /**
     * Получение записи по первичному ключу.
     *
     * @param int $id
     * @return TrunkNodeLink|null
     */
    public static function getLink($id)
    {
        return self::findOne($id);
    }
}
