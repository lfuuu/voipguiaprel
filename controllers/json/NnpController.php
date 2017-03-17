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
            ->asArray(['code', 'name'])
            ->orderBy('name')
            ->all();
    }

    /**
     * @param int $countryCode
     * @return array
     */
    public function actionCity($countryCode = 0)
    {
        $query = City::find()
            ->asArray(['id', 'name'])
            ->orderBy('name');

        (int)$countryCode && $query->andWhere(['country_code' => $countryCode]);

        return $query->all();
    }

    /**
     * @param int $countryCode
     * @return array
     */
    public function actionRegion($countryCode = 0)
    {
        $query = Region::find()
            ->asArray(['id', 'name'])
            ->orderBy('name');

        (int)$countryCode && $query->andWhere(['country_code' => $countryCode]);

        return $query->all();
    }

    /**
     * @param int $countryCode
     * @return array
     */
    public function actionOperator($countryCode = 0)
    {
        $query = Operator::find()
            ->asArray(['id', 'name'])
            ->orderBy('name');

        (int)$countryCode && $query->andWhere(['country_code' => $countryCode]);

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
