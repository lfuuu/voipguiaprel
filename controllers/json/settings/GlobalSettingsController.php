<?php

namespace app\controllers\json\settings;

use Yii;
use yii\web\HttpException;
use yii\web\Response;
use app\models\GlobalSettings;
use app\classes\BaseController;

class GlobalSettingsController extends BaseController
{
    public $layout = false;
    public $enableCsrfValidation = false;

    public function actionGet()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $settings = GlobalSettings::findOne(1);
        if (!$settings) {
            $settings = GlobalSettings::create([
                'antifraud_error_check'   => true,
                'antifraud_reject_check'  => true,
                'antifraud_timeout_check' => true,
            ]);
            $settings->save();
        }
        return $settings->attributes;
    }

    public function actionUpdate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $settings = GlobalSettings::findOne(1);
        if (!$settings) {
            throw new HttpException(404, 'Настройки не найдены.');
        }
        $settings->load(Yii::$app->request->post(), '');
        if ($settings->validate() && $settings->save()) {
            return ['status'=>'success'];
        }
        return ['status'=>'error','errors'=>$settings->errors];
    }

    public function actionCallProcedure()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $proc = Yii::$app->request->post('procedure');
        if (!$proc) {
            throw new HttpException(400, 'Не указан procedure');
        }
        $allowed = [
            'public.set_antifraud_error_accept',
            'public.set_antifraud_error_manual',
            'public.set_antifraud_reject_accept',
            'public.set_antifraud_reject_manual',
            'public.set_antifraud_timeout_accept',
            'public.set_antifraud_timeout_manual',
        ];
        if (!in_array($proc, $allowed, true)) {
            throw new HttpException(400, 'Процедура не разрешена');
        }
        Yii::$app->db->createCommand("CALL {$proc}()")->execute();
        $settings = GlobalSettings::findOne(1);
        return $settings->attributes;
    }
}
