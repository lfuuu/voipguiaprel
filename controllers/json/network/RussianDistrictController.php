<?php

namespace app\controllers\json\network;

use app\models\calligrapher\RussianDistrict;
use Yii;
use yii\web\HttpException;
use app\classes\BaseController;

class RussianDistrictController extends BaseController
{
    public $layout = false;
    public $enableCsrfValidation = false;

    /**
     * Экшен для получения списка федеральных округов (районов) РФ.
     * Возвращает JSON-массив всех записей из таблицы calligrapher.russian_district.
     *
     * @return array
     */
    public function actionRead()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $districts = RussianDistrict::find()->asArray()->all();
        return $districts;
    }
}
