<?php

namespace app\models;

use app\queries\PrefixlistPrefixQuery;

class PrefixlistPrefix extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.prefixlist_prefix';
    }

    public static function find()
    {
        return new PrefixlistPrefixQuery(get_called_class());
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