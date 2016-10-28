<?php

namespace app\controllers;

use app\exceptions\FormValidationException;
use app\exceptions\XxxException;
use Yii;
use yii\base\InvalidRouteException;
use yii\db\Exception;
use yii\filters\AccessControl;
use app\classes\BaseController;
use app\forms\LoginForm;
use app\models\Server;
use app\models\Hub;

class SiteController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'gen-passwd'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        Yii::error('index');
        Yii::info('index');
        Yii::info('index');
        Yii::info('index');
        return $this->render('index', [
            'hubs' => Hub::find()->with('servers')->all(),
            'servers' => Server::find()->where('hub_id is null')->all(),
        ]);
    }

    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionGenPasswd()
    {
        return $this->render('gen_passwd');
    }

    public function actionAbout()
    {
        return $this->render('about');
    }
}
