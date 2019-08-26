<?php

namespace app\models;
use app\queries\ActionLogQuery;

/**
 * @property int $id
 * @property int $user_id
 * @property string $controller
 * @property string $action
 * @property int $object_id
 * @property string $request_date
 * @property string $data_before
 * @property string $data_after
 */
class ActionLog extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'public.action_log';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['controller', 'action', 'request_date', 'data_before', 'data_after'], 'string'],
            [['user_id', 'object_id'], 'integer']
        ];
    }

    public static function find()
    {
        return new ActionLogQuery(get_called_class());
    }
    
    /**
     * @param array|null $data
     * @return ActionLog
     */
    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }
}