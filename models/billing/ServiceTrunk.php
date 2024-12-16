<?php

namespace app\models\billing;

use Yii;
use app\models\stat\ClientContractType;

/**
 * This is the model class for table "billing.service_trunk".
 *
 * @property int $id
 * @property int $server_id
 * @property int $client_account_id
 * @property int $trunk_id
 * @property string $activation_dt
 * @property string $expire_dt
 * @property bool $orig_enabled
 * @property bool $term_enabled
 * @property float $orig_min_payment
 * @property float $term_min_payment
 * @property int|null $operator_id
 * @property int|null $contract_id
 * @property string|null $contract_number
 * @property int|null $contract_type_id
 * @property string|null $ip
 * @property string|null $transit_price
 * @property bool|null $uplink_enabled
 * @property string|null $description
 *
 * @property ClientContractType $contractType
 */
class ServiceTrunk extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'billing.service_trunk';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'server_id', 'client_account_id', 'trunk_id', 'activation_dt', 'expire_dt'], 'required'],
            [['id', 'server_id', 'client_account_id', 'trunk_id', 'operator_id', 'contract_id', 'contract_type_id'], 'integer'],
            [['activation_dt', 'expire_dt'], 'safe'],
            [['orig_enabled', 'term_enabled', 'uplink_enabled'], 'boolean'],
            [['orig_min_payment', 'term_min_payment'], 'number'],
            [['transit_price'], 'number', 'max' => 99999999.9999],
            [['description'], 'string'],
            [['contract_number', 'ip'], 'string', 'max' => 255],
            [['contract_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ClientContractType::class, 'targetAttribute' => ['contract_type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'server_id' => 'Server ID',
            'client_account_id' => 'Client Account ID',
            'trunk_id' => 'Trunk ID',
            'activation_dt' => 'Activation Date',
            'expire_dt' => 'Expire Date',
            'orig_enabled' => 'Origination Enabled',
            'term_enabled' => 'Termination Enabled',
            'orig_min_payment' => 'Origination Minimum Payment',
            'term_min_payment' => 'Termination Minimum Payment',
            'operator_id' => 'Operator ID',
            'contract_id' => 'Contract ID',
            'contract_number' => 'Contract Number',
            'contract_type_id' => 'Contract Type ID',
            'ip' => 'IP Address',
            'transit_price' => 'Transit Price',
            'uplink_enabled' => 'Uplink Enabled',
            'description' => 'Description',
        ];
    }

    /**
     * Gets query for [[ContractType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContractType()
    {
        return $this->hasOne(ClientContractType::class, ['id' => 'contract_type_id']);
    }

    /**
     * @param int $trunkId
     * @return self[]
     */
    public static function findActualByTrunkId($trunkId)
    {
        return self::find()
            ->where(['trunk_id' => $trunkId])
            ->andWhere(['<=', 'activation_dt', new \yii\db\Expression('NOW()')])
            ->andWhere(['>=', 'expire_dt', new \yii\db\Expression('NOW()')])
            ->with('contractType') // Eager loading связи
            ->all();
    }
}
