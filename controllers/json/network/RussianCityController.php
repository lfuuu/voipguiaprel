<?php

namespace app\controllers\json\network;

use app\models\calligrapher\RussianCity;
use Yii;
use yii\web\HttpException;
use app\classes\BaseController;

class RussianCityController extends BaseController
{
    public $layout = false;
    public $enableCsrfValidation = false;

    /**
     * Экшен для получения списка городов РФ.
     * Возвращает JSON-массив всех записей из таблицы calligrapher.russian_city.
     *
     * @return array
     */
    public function actionRead()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $cities = RussianCity::find()->asArray()->all();
        return $cities;
    }
}
