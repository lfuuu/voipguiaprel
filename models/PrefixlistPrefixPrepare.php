<?php

namespace app\models;

use app\queries\PrefixlistPrefixPrepareQuery;

class PrefixlistPrefixPrepare extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.prefixlist_prefix_prepare';
    }

    public static function find()
    {
        return new PrefixlistPrefixPrepareQuery(get_called_class());
    }
    
    public function rules()		
    {		
        return [		
            [['prefix'], 'string', 'max' => 20],		
            [['prefix'], 'match', 'pattern' => '/^\d*(\[\d+\]|)+$/'],		
        ];		
    }

    public static function create(Prefixlist $prefixlist, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->prefixlist_id = $prefixlist->id;
        return $item;
    }

    public static function deleteByPrefixlist(Prefixlist $prefixlist)
    {
        return self::deleteAll(['prefixlist_id' => $prefixlist->id]);
    }
}
