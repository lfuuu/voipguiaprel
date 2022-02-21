<?php
namespace app\models\auth;

/**
 * @property int $id
 * @property int $camel_trunk_id
 * @property int $camel_server_id
 * @property int $allow
 * @property int $order
 * @property int $prefixlist_id
 * @property int $object_comment
 */
class CamelGtRule extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.camel_gt_rules';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['camel_trunk_id', 'camel_server_id', 'prefixlist_id', 'order'], 'integer'],
            [['object_comment'], 'string'],
            [['allow'], 'boolean'],
        ];
    }

    /**
     * @param CamelTrunk $camelTrunk
     * @param array|null $data
     * @return CamelGtRule
     */
    public static function create(CamelTrunk $camelTrunk, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->camel_trunk_id = $camelTrunk->id;
        $item->camel_server_id = $camelTrunk->server_id;

        return $item;
    }

    /**
     * @param CamelTrunk $camelTrunk
     * @return int
     */
    public static function deleteByTrunk(CamelTrunk $camelTrunk)
    {
        return self::deleteAll(['camel_trunk_id' => $camelTrunk->id]);
    }
}
