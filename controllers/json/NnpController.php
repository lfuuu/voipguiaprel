<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\models\nnp\City;
use app\models\nnp\Country;
use app\models\nnp\Destination;
use app\models\nnp\NdcType;
use app\models\nnp\Operator;
use app\models\nnp\Region;
use yii\db\Expression;
use Yii;

class NnpController extends JsonController
{

    const COUNTRY_CODE_RUSSIA = 643;
    
    /**
     * @return array
     */
    public function actionDestination()
    {
        return Destination::find()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->asArray()
            ->all();
    }

    /**
     * @return array
     */
    public function actionCountry()
    {
        return Country::find()
            ->select(['code', 'name_rus'])
            ->orderBy('name_rus')
            ->asArray()
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
            ->select(['id', new Expression('case when country_code = ' . self::COUNTRY_CODE_RUSSIA . ' then name else name_translit end as name')])
            ->where(['country_code' => $countryCode])
            ->asArray()
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
            ->select(['id', new Expression('case when country_code = ' . self::COUNTRY_CODE_RUSSIA . ' then name else name_translit end as name')])
            ->where(['country_code' => $countryCode])
            ->andWhere(['region_id' => $region])
            ->asArray()
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
            ->select(['id', new Expression('case when country_code = ' . self::COUNTRY_CODE_RUSSIA . ' then name else name_translit end as name')])
            ->where(['country_code' => $countryCode])
            ->asArray()
            ->orderBy('name');

        return $query->all();
    }

    /**
     * @return array
     */
    public function actionNdcType()
    {
        return NdcType::find()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->asArray()
            ->all();
    }

}
