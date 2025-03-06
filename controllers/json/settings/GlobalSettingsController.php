<?php

namespace app\controllers\json\settings;

use Yii;
use app\models\GlobalSettings;
use app\classes\BaseController;
use yii\web\HttpException;

class GlobalSettingsController extends BaseController
{
    // Отключаем layout для JSON-ответов
    public $layout = false;
    public $enableCsrfValidation = false;

    /**
     * Возвращает глобальные настройки в формате JSON.
     *
     * Если запись с id = 1 не найдена, создаёт новую с настройками по умолчанию.
     *
     * @return array
     */
    public function actionGet()
    {
        $settings = GlobalSettings::findOne(1);
        if (!$settings) {
            $settings = GlobalSettings::create([
                'antifraud_timeout_check' => true,
                'antifraud_error_check'   => true,
                'antifraud_reject_check'  => true,
            ]);
            $settings->save();
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $settings->attributes;
    }

    /**
     * Обновляет глобальные настройки.
     *
     * Ожидается, что данные передаются методом POST.
     * Возвращает JSON-ответ с результатом операции.
     *
     * @return array
     * @throws HttpException
     */
    public function actionUpdate()
    {
        $settings = GlobalSettings::findOne(1);
        if (!$settings) {
            throw new HttpException(404, 'Настройки не найдены.');
        }

        $data = Yii::$app->request->post();
        $settings->load($data, '');
        if ($settings->validate() && $settings->save()) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['status' => 'success', 'settings' => $settings->attributes];
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['status' => 'error', 'errors' => $settings->errors];
    }

    /**
     * Отображает модальное окно с глобальными настройками.
     *
     * Это действие используется для формирования HTML-разметки модалки,
     * в которой будут переключатели, значения которых сохраняются в БД.
     *
     * @return string
     */
    public function actionModal()
    {
        $settings = GlobalSettings::findOne(1);
        if (!$settings) {
            $settings = GlobalSettings::create([
                'antifraud_timeout_check' => true,
                'antifraud_error_check'   => true,
                'antifraud_reject_check'  => true,
            ]);
            $settings->save();
        }

        $this->layout = 'minimal';
        return $this->render('modal', [
            'settings' => $settings,
        ]);
    }

    /**
     * Вызывает указанную хранимую процедуру.
     *
     * Ожидается, что параметр 'procedure' передаётся методом POST.
     * Допускаются только определённые хранимые процедуры для безопасности.
     *
     * @return array JSON ответ с результатом вызова процедуры.
     * @throws HttpException
     */
    public function actionCallProcedure()
    {
        $procedure = Yii::$app->request->post('procedure');
        if (!$procedure) {
            throw new HttpException(400, 'Не указан параметр procedure.');
        }

        $allowedProcedures = [
            'public.set_antifraud_error_accept',
            'public.set_antifraud_error_manual',
            'public.set_antifraud_reject_accept',
            'public.set_antifraud_reject_manual',
            'public.set_antifraud_timeout_accept',
            'public.set_antifraud_timeout_manual',
        ];

        if (!in_array($procedure, $allowedProcedures)) {
            throw new HttpException(400, 'Указанная процедура не разрешена.');
        }

        $sql = "SELECT {$procedure}()";

        try {
            $result = Yii::$app->db->createCommand($sql)->queryOne();
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['status' => 'success', 'result' => $result];
        } catch (\Exception $e) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }
}
