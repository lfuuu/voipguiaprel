<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\models\billing\VoipNumber;
use app\models\billing\VoipRegistry;
use app\models\nnp\City;
use app\models\nnp\Country;
use app\models\nnp\Destination;
use app\models\nnp\NdcType;
use app\models\nnp\NumberRange;
use app\models\nnp\Region;
use app\models\geo\Country as GeoCountry;
use app\models\geo\City as GeoCity;
use app\models\materialized_view\Ndc;
use app\models\materialized_view\Operator;
use app\models\nnp\RouteMnc;
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
            ->select(['code', 'name_rus', 'mcc'])
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
            ->select(['id', 'region_code_fz', new Expression('case when country_code = ' . self::COUNTRY_CODE_RUSSIA . ' then name else name_translit end as name')])
            ->where(['country_code' => $countryCode])
            ->asArray()
            ->orderBy('name');

        return $query->all();
    }

    /**
     * @return array
     */
    public function actionNdc()
    {
        $countryCode = $this->request['country_code'];

        if (empty($countryCode)) {
            return [];
        }

        $query = Ndc::find()
            ->select(['id', 'name'])
            ->where(['country_code' => $countryCode])
            ->asArray();

        return $query->all();
    }

    /**
     * @return array
     */
    public function actionCity()
    {
        $countryCode = $this->request['country_code'];
        $region = isset($this->request['region']) ? $this->request['region'] : false;
    
        if (empty($countryCode) || $region == [0 => ''] || empty($region)) {
            return [];
        }
        
        if ($region) {
            $query = City::find()
                ->select(['id', new Expression('case when country_code = ' . self::COUNTRY_CODE_RUSSIA . ' then name else name_translit end as name')])
                ->where(['country_code' => $countryCode])
                ->andWhere(['region_id' => $region])
                ->asArray()
                ->orderBy('name');
        } else {
            $query = City::find()
                ->select(['id', new Expression('case when country_code = ' . self::COUNTRY_CODE_RUSSIA . ' then name else name_translit end as name')])
                ->where(['country_code' => $countryCode])
                ->asArray()
                ->orderBy('name');
        }

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
            ->select(['id', 'name'])
            ->where(['country_code' => $countryCode])
            ->asArray();

        return $query->all();
    }

    /**
     * @return array
     */
    public function actionRouteMnc()
    {        
        $query = RouteMnc::find()
            ->select(['mnc'])
            ->distinct()
            ->asArray();
        
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
    
    /**
     * @return array
     */
    public function actionGeoCountry()
    {
        return GeoCountry::find()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->asArray()
            ->all();
    }
    
    /**
     * @return array
     */
    public function actionGeoCity()
    {
        return GeoCity::find()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->asArray()
            ->all();
    }
    
    /**
     * @return array
     */
    public function actionSource()
    {
        $query = VoipRegistry::find()
            ->select(['source as id', 'source as name'])
            ->distinct()
            ->asArray()
            ->orderBy('source');
        
        return $query->all();
    }
    
    /**
     * @return array
     */
    public function actionNumberSource()
    {
        $query = VoipNumber::find()
            ->select(['source as id', 'source as name'])
            ->distinct()
            ->asArray()
            ->orderBy('source');
        
        return $query->all();
    }
    
    /**
     * @return array
     */
    public function actionNumberStatus()
    {
        $query = VoipNumber::find()
            ->select(['status as id', 'status as name'])
            ->distinct()
            ->asArray()
            ->orderBy('status');
        
        return $query->all();
    }
}
