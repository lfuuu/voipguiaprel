<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\Currency;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class CurrencyController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('cpc_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $currencies = Currency::find()
                ->select(['code'])
                ->orderBy('id')
                ->asArray()
                ->all();
                
        foreach($currencies as $currency) {
            $currencyList[] = [
                'id' => $currency['code'],
                'name' => $currency['code'],
            ]; 
        }
        
        return $currencyList;
    }
}
