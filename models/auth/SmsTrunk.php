<?php

namespace app\models\auth;

use app\queries\auth\SmsTrunkQuery;
use yii\web\ForbiddenHttpException;

/**
 * @property int         $id
 * @property string      $name
 * @property int         $server_id
 * @property int         $a2psms_route_table_id
 * @property string      $route_name
 * @property int|null    $sms_gate_id
 * @property int|null    $connector_type_id
 * @property int|null $connector_proto_id
 *
 *
 *
 */
class SmsTrunk extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.a2psms_route';
    }

    public static function find()
    {
        return new SmsTrunkQuery(get_called_class());
    }

    public static function create(array $data = null)
    {
        $item = new self();
        if ($data !== null) {
            $item->load($data, '');
        }
        return $item;
    }

    public function rules()
    {
        return [
            [['name', 'route_name'], 'string'],
            [['id', 'server_id', 'a2psms_route_table_id'], 'integer'],

            [['sms_gate_id', 'connector_type_id', 'connector_proto_id'], 'integer'],

            [['sms_gate_id', 'connector_type_id', 'connector_proto_id'], 'safe'],
        ];
    }

    /**
     * Связь: конфигурация SMPP
     * @return \yii\db\ActiveQuery
     */
    public function getSmppConfig()
    {
        return $this->hasOne(SmsTrunkSmppConfig::class, ['trunk_id' => 'id']);
    }

    /**
     * Связь: конфигурация REST/API
     * @return \yii\db\ActiveQuery
     */
    public function getApiConfig()
    {
        return $this->hasOne(SmsTrunkApiConfig::class, ['trunk_id' => 'id']);
    }

    /**
     * @throws ForbiddenHttpException
     * Перед сохранением проверим права
     */
    public function beforeSave($insert)
    {
        if (!\Yii::$app->user->can($insert ? 'sms_trunk_create' : 'sms_trunk_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        return parent::beforeSave($insert);
    }
}
