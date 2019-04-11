<?php

namespace app\models;

/**
 * @property int $id
 * @property int $trunk_id
 * @property bool $orig - Оригинация / Терминация
 * @property bool $outgoing - A/B номер
 * @property bool $allow
 * @property int $order
 * @property int $prefixlist_id
 * @property bool $test_redirect_num
 * @property int $abc_mode
 */
class TrunkABfiltersRule extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.trunk_abfilters_rule';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['outgoing', 'allow', 'orig',], 'boolean'],
            [['prefixlist_id', 'order', 'trunk_id', 'abc_mode'], 'integer'],
            ['trunk_id', 'required'],
        ];
    }

    /**
     * @param Trunk $trunk
     * @param array|null $data
     * @return TrunkABfiltersRule
     */
    public static function create(Trunk $trunk, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->trunk_id = $trunk->id;
        return $item;
    }

    /**
     * @param Trunk $trunk
     * @return int
     */
    public static function deleteByTrunk(Trunk $trunk)
    {
        return self::deleteAll(['trunk_id' => $trunk->id]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrefixlist()
    {
        return $this->hasOne(Prefixlist::className(), ['id' => 'prefixlist_id']);
    }

}