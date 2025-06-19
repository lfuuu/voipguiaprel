<?php

namespace app\models\calligrapher;

use yii\db\ActiveRecord;
use app\classes\traits\ModelRules;

/**
 * Модель для таблицы "calligrapher.node_link"
 *
 * @property int         $node_link_id
 * @property int         $src_node_id
 * @property int         $dst_node_id
 * @property bool        $unidirect
 * @property int         $weight
 * @property string|null $comment
 * @property string|null $src_trunk_name
 * @property string|null $dst_trunk_name
 * @property string|null $trunk_name
 * @property string|null $start_date    // TIMESTAMP без часового пояса
 * @property string|null $end_date      // TIMESTAMP без часового пояса
 */
class NodeLink extends ActiveRecord
{
    use ModelRules;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'calligrapher.node_link';
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
            [['src_node_id', 'dst_node_id'], 'required'],

            // целочисленные поля
            [['src_node_id', 'dst_node_id', 'weight'], 'integer'],

            // булево поле
            [['unidirect'], 'boolean'],

            // текстовые поля
            [['comment', 'src_trunk_name', 'dst_trunk_name', 'trunk_name'], 'string'],

            // даты — безопасные для массовой записи
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

        foreach (['comment', 'src_trunk_name', 'dst_trunk_name', 'trunk_name'] as $attr) {
            if ($this->$attr !== null && trim($this->$attr) === '') {
                $this->$attr = null;
            }
        }

        return true;
    }

    /**
     * Создание новой записи модели.
     *
     * @param array|null $data
     * @return NodeLink
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
}
