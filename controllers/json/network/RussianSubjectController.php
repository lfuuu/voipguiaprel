<?php

namespace app\controllers\json\network;

use app\models\calligrapher\RussianSubject;
use Yii;
use yii\web\HttpException;
use app\classes\BaseController;

class RussianSubjectController extends BaseController
{
    public $layout = false;
    public $enableCsrfValidation = false;

    /**
     * Экшен для получения списка субъектов РФ.
     * Возвращает JSON-массив всех записей из таблицы calligrapher.russian_subject.
     *
     * @return array
     */
    public function actionRead()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $subjects = RussianSubject::find()->asArray()->all();
        return $subjects;
    }
}
