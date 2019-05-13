<?php
namespace app\models;

/**
 * @property int $id
 * @property int $route_table_id
 * @property int $order
 * @property int $trunk_group_id
 * @property int $number_id_filter_a
 * @property int $number_id_filter_b
 * @property int $number_id_filter_c
 * @property bool $allow
 * @property string $object_comment
 */
class RouteRouteRule extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.route_route_rule';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['trunk_group_id', 'number_id_filter_a', 'number_id_filter_b', 'number_id_filter_c'], 'integer'],
            [['allow',], 'boolean'],
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
        ];
    }

    /**
     * @param RouteTable $table
     * @param array|null $data
     * @return RouteRouteRule
     */
    public static function create(RouteTable $table, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->route_table_id = $table->id;
        return $item;
    }

    /**
     * @param RouteTable $table
     * @return int
     */
    public static function deleteByRouteTable(RouteTable $table)
    {
        return self::deleteAll(['route_table_id' => $table->id]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrunkGroup()
    {
        return $this->hasOne(TrunkGroup::className(), ['id' => 'trunk_group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNumberA()
    {
        return $this->hasOne(Number::className(), ['id' => 'number_id_filter_a']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNumberB()
    {
        return $this->hasOne(Number::className(), ['id' => 'number_id_filter_b']);
    }

}