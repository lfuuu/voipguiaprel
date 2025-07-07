<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "billing_server".
 *
 * @property int         $id
 * @property string      $name
 * @property string|null $ip
 * @property string|null $contact_info
 * @property string|null $interface_url
 * @property string|null $description
 * @property array|null  $dashboards
 * @property bool|null   $antifraud_incoming_accept
 * @property bool|null   $antifraud_proxy_timeout
 * @property int|null    $antifraud_proxy_timeout_prefixlist_id
 * @property string|null $address
 * @property string|null $type             Тип сервера (billing_voip, ocslte, billing_api)
 */
class BillingServer extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'billing_server';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // обязательные поля
            [['id', 'name', 'type'], 'required'],

            // строковые поля
            ['ip', 'string', 'max' => 255],
            ['interface_url', 'string', 'max' => 255],
            [['contact_info', 'description', 'address'], 'string'],
            [['dashboards'], 'safe'],   // jsonb

            // булевы флаги
            [['antifraud_incoming_accept', 'antifraud_proxy_timeout'], 'boolean'],

            // целочисленные поля
            [['antifraud_proxy_timeout_prefixlist_id', 'id'], 'integer'],

            // длина названия
            ['name', 'string', 'max' => 255],

            // валидация поля type
            ['type', 'in', 'range' => ['billing_voip', 'ocslte', 'billing_api']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                                 => 'ID сервера',
            'name'                               => 'Название',
            'ip'                                 => 'IP адрес',
            'contact_info'                       => 'Контактная информация',
            'address'                            => 'Физический адрес',
            'interface_url'                      => 'URL интерфейса',
            'dashboards'                         => 'Dashboards (JSON)',
            'antifraud_incoming_accept'          => 'Принимать входящий антифрод',
            'antifraud_proxy_timeout'            => 'Действие: Timeout API запроса',
            'antifraud_proxy_timeout_prefixlist_id' => 'Префикс-лист для Timeout',
            'description'                        => 'Описание сервера',
            'type'                               => 'Тип сервера',
        ];
    }
}
