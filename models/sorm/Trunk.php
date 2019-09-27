<?php

namespace app\models\sorm;
use app\queries\sorm\TrunkQuery;

/**
 * @property integer $id
 * @property integer $operator_id
 * @property integer $code_trunk
 * @property string $ats_mnemo_code
 * @property integer $type
 * @property string $start_date
 * @property string $stop_date
 * @property string $name
 * @property string $old_name
 * @property boolean $is_ip
 * @property boolean $is_show
 * @property integer[] $groups
 * @property integer $region_id
 * @property integer $sorm_operator_id
 * @property string $trunk_ip
 * @property boolean $is_local
 * @property string $border_ats_code
 * @property integer $source_type_id
 * @property string $ip_addr
 * @property string $object_comment
 * @property
 */
class Trunk extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'copm.trunk';
    }

    public static function find()
    {
        return new TrunkQuery(get_called_class());
    }

    public function rules()
    {
        return [
            [['ats_mnemo_code', 'start_date', 'stop_date', 'name', 'old_name', 'trunk_ip', 'groups', 'ip_addr', 'sorm_operator_id'], 'string'],
            [['operator_id', 'code_trunk', 'type', 'region_id', 'source_type_id'], 'integer'],
            [['is_ip', 'is_show'], 'boolean'],
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
        ];
    }
    
    /**
     * @param array|null $data
     * @return Trunk
     */
    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }
}