<?php

namespace app\controllers;

use app\exceptions\XxxException;
use app\classes\BaseController;

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
}
