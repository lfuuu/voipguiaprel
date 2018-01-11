<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\models\nnp\City;
use app\models\nnp\Country;
use app\models\nnp\Destination;
use app\models\nnp\NdcType;
use app\models\nnp\Operator;
use app\models\nnp\Region;
use Yii;

class NnpController extends JsonController
{

    /**
     * @return array
     */
    public function actionDestination()
    {
        return Destination::find()
            ->asArray(['id', 'name'])
            ->orderBy('name')
            ->all();
    }

    /**
     * @return array
     */
    public function actionCountry()
    {
        return Country::find()
            ->asArray(['code', 'name_rus'])
            ->orderBy('name')
            ->all();
    }

    /**
     * @return array
     */
    public function actionRegion()
    {
        $countryCode = $this->request['country_code'];
        
        if (empty($countryCode)) {
            return [];
        }
        
        $query = Region::find()
            ->asArray(['id', 'name'])
            ->where(['country_code' => $countryCode])
            ->orderBy('name');

        return $query->all();
    }

    /**
     * @return array
     */
    public function actionCity()
    {
        $countryCode = $this->request['country_code'];
        $region = $this->request['region'];
    
        if (empty($countryCode) || empty($region)) {
            return [];
        }
        
        $query = City::find()
            ->asArray(['id', 'name'])
            ->where(['country_code' => $countryCode])
            ->andWhere(['region_id' => $region])
            ->orderBy('name');

        return $query->all();
    }

    /**
     * @return array
     */
    public function actionOperator()
    {
        $countryCode = $this->request['country_code'];
    
        if (empty($countryCode)) {
            return [];
        }
        
        $query = Operator::find()
            ->asArray(['id', 'name'])
            ->where(['country_code' => $countryCode])
            ->orderBy('name');

        return $query->all();
    }

    /**
     * @return array
     */
    public function actionNdcType()
    {
        return NdcType::find()
            ->asArray(['id', 'name'])
            ->orderBy('name')
            ->all();
    }

}
