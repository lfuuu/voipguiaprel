<?php

namespace app\controllers;

use Yii;
use app\exceptions\XxxException;
use app\classes\BaseController;
use app\models\ServerOcs;
use app\models\ChangePasswordForm;

class CategoryController extends BaseController
{
    public function actionRouting()
    {
        return $this->render('routing', [
            'hubs' => Hub::find()->orderBy('id')->with('servers', 'servers.preparedPrefixlists','instanceSettings')->all(),
            'servers' => Server::find()->where('hub_id is null')->with('instanceSettings', 'preparedPrefixlists')->orderBy('name')->all(),
        ]);
    }
    
    public function actionBilling()
    {
        return $this->render('billing', []);
    }
    
    public function actionMarketplace()
    {
        return $this->render('marketplace', []);
    }
    
    public function actionMarketplaceEu()
    {
        return $this->render('marketplace-eu', []);
    }

    public function actionSettings()
    {
        return $this->render('settings', []);
    }

    public function actionSms()
    {
        return $this->render('sms', [
            'servers' => ServerOcs::find()->where(['id' => 9])->orderBy('id')->one(),
        ]);
    }

    public function actionCamel()
    {
        return $this->render('camel', [
            'servers' => ServerOcs::find()->where(['type' => 'ocslte'])->orderBy('id')->all(),
        ]);
    }

    public function actionApiBilling()
    {
        return $this->render('api_billing', [
            'servers' => ServerOcs::find()->where(['type' => 'apibill'])->orderBy('id')->all(),
        ]);
    }

    public function actionChangePassword()
    {
        $model = new ChangePasswordForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->changePassword()) {
            Yii::$app->session->setFlash('success', 'Пароль успешно изменен');
            return $this->redirect(['site/index']);
        }

        return $this->render('change-password', [
            'model' => $model,
        ]);
    }
}
