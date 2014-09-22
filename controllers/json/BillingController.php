<?php

namespace app\controllers\json;

use app\models\billing\NetworkType;
use Yii;
use app\classes\JsonController;
use app\models\billing\GeoCountry;
use app\models\billing\GeoRegion;
use app\models\billing\GeoOperator;
use app\models\billing\Geo;


class BillingController extends JsonController
{
    public function actionCountries()
    {
        return GeoCountry::find()->asArray(['id', 'name'])->orderBy('name')->all();
    }

    public function actionRegions()
    {
        return GeoRegion::find()->asArray(['id', 'name'])->orderBy('name')->all();
    }

    public function actionCities()
    {
        $query =
            Geo::find()
                ->distinct()
                ->select(['city as id', 'city_name as name'])
                ->where('city > 0')
                ->orderBy('city_name')
            ;
        if (isset($this->request['rossvyaz_country_id']) && $this->request['rossvyaz_country_id'] > 0) {
            $query->andWhere(['country' => $this->request['rossvyaz_country_id']]);
        }
        if (isset($this->request['rossvyaz_region_id']) && $this->request['rossvyaz_region_id'] > 0) {
            $query->andWhere(['region' => $this->request['rossvyaz_region_id']]);
        }
        return $query->asArray()->all();
    }

    public function actionOperators()
    {
        return GeoOperator::find()->asArray(['id', 'name'])->orderBy('name')->all();
    }

    public function actionNetworkTypes()
    {
        return NetworkType::find()->asArray(['id', 'name'])->orderBy('id')->all();
    }
}
