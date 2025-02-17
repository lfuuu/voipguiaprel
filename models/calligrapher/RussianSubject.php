<?php

namespace app\models\calligrapher;

use app\classes\traits\ModelRules;
use yii\db\ActiveRecord;

/**
 * Модель для таблицы "calligrapher.russian_subject"
 *
 * @property int    $russian_subject_id
 * @property int    $russian_district_id
 * @property string $russian_subject
 */
class RussianSubject extends ActiveRecord
{
    use ModelRules;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'calligrapher.russian_subject';
    }

    /**
     * Правила валидации.
     */
    private static function rulesStatic()
    {
        return [
            [['russian_subject_id', 'russian_district_id'], 'integer'],
            [['russian_subject'], 'string'],
        ];
    }

    /**
     * Создание записи.
     */
    public static function create(array $data = null)
    {
        $subject = new self();
        $subject->load($data, '');
        return $subject;
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
    public static function getSubject($id)
    {
        return self::findOne($id);
    }
}
