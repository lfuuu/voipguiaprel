<?php
namespace app\models\stat;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "stat.client_contract_type".
 *
 * @property int $id
 * @property string $name
 */
class ClientContractType extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stat.client_contract_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название Типа Контракта',
        ];
    }
}
