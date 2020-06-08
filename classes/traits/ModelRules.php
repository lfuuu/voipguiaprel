<?php

namespace app\classes\traits;

trait ModelRules
{
    /**
     * @return array
     */
    public function rules()
    {
        return self::rulesStatic();
    }
    
    public static function rulesFlat()
    {
        $tempResult = self::rulesStatic();
        $result = ['id'];
        
        foreach ($tempResult as $tempResultItem) {
            $result = array_merge($result, $tempResultItem[0]);
        }
        
        return $result;
    }
}
