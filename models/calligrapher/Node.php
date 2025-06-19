<?php

namespace app\models\calligrapher;

use app\classes\traits\ModelRules;
use yii\db\ActiveRecord;

/**
 * Модель для таблицы "calligrapher.node"
 *
 * @property int          $node_id
 * @property string       $node_name_id
 * @property int|null     $node_type_id
 * @property int|null     $region_id
 * @property string|null  $comment
 * @property string|null  $ipaddress
 * @property string|null  $address
 * @property int|null     $status
 * @property int|null     $node_status_id
 * @property int|null     $russian_district_id
 * @property int|null     $russian_subject_id
 * @property int|null     $russian_city_id
 * @property int|null     $graph_id
 * @property string|null  $type
 * @property string|null  $net_type
 * @property string|null  $phone_idx
 * @property string|null  $ss7_spc
 * @property string|null  $start_date   // формат TIMESTAMP
 * @property string|null  $end_date     // формат TIMESTAMP
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
            // Текстовые поля
            [['node_name_id', 'comment', 'address', 'type', 'net_type', 'phone_idx', 'ss7_spc'], 'string'],
            // Целочисленные поля
            [['node_type_id', 'region_id', 'status', 'node_status_id', 'russian_district_id', 'russian_subject_id', 'russian_city_id', 'graph_id'], 'integer'],
            // IP-адрес
            [['ipaddress'], 'ip', 'ipv4' => true, 'ipv6' => true, 'skipOnEmpty' => true],
            // Даты
            [['start_date', 'end_date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return self::rulesStatic();
    }

    /**
     * Переопределение метода beforeSave для преобразования пустых строк в null.
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Если ipaddress пустое (только пробелы или пустая строка), устанавливаем null
            if ($this->ipaddress !== null && trim($this->ipaddress) === '') {
                $this->ipaddress = null;
            }
            // Преобразование пустых строк в null для остальных nullable-полей
            foreach (['comment', 'address', 'type', 'net_type', 'phone_idx', 'ss7_spc'] as $attr) {
                if ($this->$attr !== null && trim($this->$attr) === '') {
                    $this->$attr = null;
                }
            }

            return true;
        }
        return false;
    }

    /**
     * Создание новой записи модели.
     *
     * @param array|null $data
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
     * @return Node|null
     */
    public static function getNode($id)
    {
        return self::findOne($id);
    }
}
