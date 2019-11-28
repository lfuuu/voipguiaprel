<?php

namespace app\controllers;

use app\exceptions\XxxException;
use app\classes\BaseController;
use app\models\ServerOcs;

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
}
