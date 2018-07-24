<?php

namespace app\models\sorm;
use app\queries\sorm\CommutatorQuery;

/**
 * @property int $id
 * @property string $comutator_str_id
 * @property
 */
class Commutator extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'copm.comutator';
    }

    public static function find()
    {
        return new CommutatorQuery(get_called_class());
    }

    public function rules()
    {
        return [
            [['comutator_str_id'], 'string'],
        ];
    }
}