<?php

namespace app\models\nnp;

/**
 * @property int $country_code
 * @property string $ndc
 * @property string $full_number_from
 * @property string $full_number_to
 * @property string $opeator
 */
class RouteMnc extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.route_mnc';
    }

}