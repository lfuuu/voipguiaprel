<?php

namespace app\models\calligrapher;

use app\classes\traits\ModelRules;
use yii\db\ActiveRecord;

/**
 * Модель для таблицы "calligrapher.russian_city"
 *
 * @property int    $russian_city_id
 * @property string $russian_city
 * @property int    $russian_subject_id
 */
class RussianCity extends ActiveRecord
{
    use ModelRules;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'calligrapher.russian_city';
    }

    /**
     * Правила валидации.
     */
    private static function rulesStatic()
    {
        return [
            [['russian_city'], 'string'],
            [['russian_subject_id'], 'integer'],
        ];
    }

    /**
     * Создание новой записи.
     */
    public static function create(array $data = null)
    {
        $city = new self();
        $city->load($data, '');
        return $city;
    }

    /**
     * Удаление записи.
     */
    public function deleteRecord()
    {
        return $this->delete();
    }

    /**
     * Получить объект RussianCity по первичному ключу.
     */
    public static function getCity($id)
    {
        return self::findOne($id);
    }
}
