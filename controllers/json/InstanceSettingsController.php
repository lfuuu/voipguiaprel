<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\billing\GeoCountry;
use app\models\billing\Geo;
use app\exceptions\FormValidationException;

class InstanceSettingsController extends JsonController
{

    public function actionGet()
    {
        $server = $this->getServerOr404($this->request['server_id']);
        $instanceSettings = $this->getInstanceSettingsOr404($this->request['server_id']);

        return [
            'server_id' => $server->id,
            'region_id' => $instanceSettings->region_id,
            'city_geo_id' => $instanceSettings->city_geo_id,
            'name' => $instanceSettings->name,
            'city_prefix' => $instanceSettings->city_prefix,
            'country_id' => $instanceSettings->country_id,
            'countries' => $this->actionCountries(),
            'cities' => $this->actionCities(),
            'geos' => $this->actionGeo(),
            'city_id' => $instanceSettings->city_id,
        ];
    }

    public function actionSave()
    {
        $instanceSettings = $this->getInstanceSettingsOr404($this->request['server_id']);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $instanceSettings->load($this->request, '');

            if (!$instanceSettings->save()) {
                throw new FormValidationException($instanceSettings);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }

    public function actionGeo()
    {
        return Geo::find()->asArray(['id', 'name'])->orderBy('name')->all();
    }

    public function actionCountries()
    {
        return GeoCountry::find()->asArray(['id', 'name'])->orderBy('name')->all();
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
        if (isset($this->request['country_id']) && $this->request['country_id'] > 0) {
            $query->andWhere(['country' => $this->request['country_id']]);
        }
        if (isset($this->request['region_id']) && $this->request['region_id'] > 0) {
            $query->andWhere(['region' => $this->request['region_id']]);
        }
        return $query->asArray()->all();
    }
}
