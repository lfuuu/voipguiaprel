<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель для таблицы "public.global_bill_settings".
 *
 * @property int $id
 * @property bool $antifraud_timeout_check
 * @property bool $antifraud_error_check
 * @property bool $antifraud_reject_check
 */
class GlobalSettings extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.global_bill_settings';
    }

    /**
     * Создаёт новый экземпляр модели с передачей данных.
     *
     * @param array|null $data
     * @return static
     */
    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }

    /**
     * Правила валидации для атрибутов модели.
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['antifraud_timeout_check', 'antifraud_error_check', 'antifraud_reject_check'], 'boolean'],
        ];
    }
}
