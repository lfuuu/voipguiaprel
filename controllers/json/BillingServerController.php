<?php
namespace app\controllers\json;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use app\models\BillingServer;

class BillingServerController extends Controller
{
    /**
     * Отключаем CSRF и layout (только JSON)
     */
    public $enableCsrfValidation = false;
    public $layout = false;

    /**
     * Подключаем поведения для формата ответа и HTTP-методов
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Возвращать все ответы в JSON
        $behaviors['contentNegotiator'] = [
            'class'   => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];

        // Ограничить HTTP-методы
        $behaviors['verbs'] = [
            'class'   => VerbFilter::class,
            'actions' => [
                'delete' => ['POST'],
                'save'   => ['POST'],
                'get'    => ['POST'],
                'read'   => ['GET', 'POST'],
            ],
        ];

        return $behaviors;
    }

    /**
     * Список всех серверов
     */
    public function actionRead()
    {
        return BillingServer::find()->all();
    }

    /**
     * Получить один сервер по ID (POST)
     */
    public function actionGet()
    {
        $post = Yii::$app->request->post();
        if (empty($post['id'])) {
            throw new BadRequestHttpException('ID не передан');
        }
        $model = BillingServer::findOne($post['id']);
        if (!$model) {
            throw new NotFoundHttpException('Server not found');
        }
        return $model;
    }

    /**
     * Создание или обновление сервера
     */
   public function actionSave()
{
    $data = Yii::$app->request->post();

    // 1) Если явно передан old_id — обновляем именно её
    if (isset($data['old_id'])) {
        $model = BillingServer::findOne($data['old_id']);
        if (!$model) {
            throw new NotFoundHttpException("Server not found (old_id={$data['old_id']})");
        }
    }
    // 2) Иначе если передан id и такая запись уже есть — обычное обновление
    elseif (!empty($data['id']) && BillingServer::findOne($data['id'])) {
        $model = BillingServer::findOne($data['id']);
    }
    // 3) Иначе — создаём новую запись
    else {
        $model = new BillingServer();
    }

    // 4) Присваиваем все атрибуты (включая id, если был указан)
    $model->attributes = $data;

    // 5) Сохраняем и сразу возвращаем полный массив с id
    if ($model->save()) {
        return ['success' => true, 'data' => $model->toArray()];
    } else {
        return ['success' => false, 'errors' => $model->errors];
    }
}



    /**
     * Удаление сервера
     */
    public function actionDelete()
    {
        $post = Yii::$app->request->post();
        if (empty($post['id'])) {
            throw new BadRequestHttpException('ID не передан');
        }
        $model = BillingServer::findOne($post['id']);
        if (!$model) {
            throw new NotFoundHttpException('Server not found');
        }
        $model->delete();
        return ['success' => true];
    }
}

