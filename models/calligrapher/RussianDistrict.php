<?php

namespace app\models\calligrapher;

use app\classes\traits\ModelRules;
use yii\db\ActiveRecord;

/**
 * Модель для таблицы "calligrapher.russian_district"
 *
 * @property int    $russian_district_id
 * @property string $russian_district
 * @property string $russian_district_short
 */
class RussianDistrict extends ActiveRecord
{
    use ModelRules;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'calligrapher.russian_district';
    }

    /**
     * Правила валидации.
     */
    private static function rulesStatic()
    {
        return [
            [['russian_district', 'russian_district_short'], 'string'],
        ];
    }

    /**
     * Создание записи.
     */
    public static function create(array $data = null)
    {
        $district = new self();
        $district->load($data, '');
        return $district;
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
    public static function getDistrict($id)
    {
        return self::findOne($id);
    }
}
