<?php

namespace app\models;
use app\queries\RouteCaseQuery;
use yii\db\Query;

/**
 * @property int $id
 * @property int $server_id
 * @property string $name
 * @property string $object_comment
 * @property
 */
class RouteCase extends \yii\db\ActiveRecord
{
    public $_subitems = [
        'trunks' => 'getTrunks'
    ];
    
    public static function tableName()
    {
        return 'auth.route_case';
    }

    public static function find()
    {
        return new RouteCaseQuery(get_called_class());
    }

    public static function create(Server $server, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->server_id = $server->id;
        return $item;
    }

    public function rules()
    {
        return [
            [['sw_shared'], 'boolean'],
            [['name'], 'string', 'min' => 4, 'max' => 50],
            [['name'], 'match', 'pattern' => '/^\w+$/'],
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
        ];
    }

    public function extraFields()
    {
        return ['operators'];
    }

    public function getTrunks()
    {
        return $this->hasMany(RouteCaseTrunk::className(), ['route_case_id' => 'id'])->orderBy('priority');
    }

    /**
     * @return array
     */
    public function findUsagesInOutcomes()
    {
        return
            (new Query)
                ->select(['o.*'])
                ->from(Outcome::tableName() . ' as o')
                ->innerJoin(RouteCase::tableName() . ' as r', 'r.id = o.route_case_id')
                ->where('r.id = ' . $this->id)
                ->all();
    }
}