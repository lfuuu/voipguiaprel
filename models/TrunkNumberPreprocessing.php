<?php
namespace app\models;
use app\queries\TrunkNumberPreprocessingQuery;

/**
 * @property int $id
 * @property int $trunk_id
 * @property bool $src
 * @property int $order
 * @property int $noa
 * @property int $length
 * @property string $prefix
 * @property string $abc_mode
 * @property string $object_comment
 * @property
 */
class TrunkNumberPreprocessing extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.trunk_number_preprocessing';
    }

    public static function find()
    {
        return new TrunkNumberPreprocessingQuery(get_called_class());
    }

    public static function create(Trunk $trunk, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->trunk_id = $trunk->id;
        return $item;
    }

    public static function deleteByTrunk(Trunk $trunk)
    {
        return self::deleteAll(['trunk_id' => $trunk->id]);
    }

    public function rules()
    {
        return [
            [['src'], 'boolean'],
            [['noa'], 'integer', 'min' => 0, 'max' => 3],
            [['length'], 'integer', 'min' => 1, 'max' => 20],
            [['prefix'], 'string', 'min' => 1,  'max' => 10],
            [['abc_mode'], 'integer'],
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
        ];
    }
}