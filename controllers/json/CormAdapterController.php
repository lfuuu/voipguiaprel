<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\sorm\TelemetryReceiver;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

class CormAdapterController extends JsonController
{
    public function actionList()
{
    if (!\Yii::$app->user->can('corm_edit')) {
        Yii::error('Access denied for user: ' . Yii::$app->user->id);
        throw new ForbiddenHttpException('Access denied');
    }

    if (!isset($this->request['server_id'])) {
        throw new HttpException(400, 'Missing server_id');
    }

    $server = $this->getServerOr404($this->request['server_id']);

    return TelemetryReceiver::find()
        ->select(['id', 'name'])
        ->where(['server_id' => $server->id])
        ->orderBy('name')
        ->asArray()
        ->all();
}

public function actionGetOne()
{
    if (!\Yii::$app->user->can('corm_edit')) {
        throw new ForbiddenHttpException('Access denied');
    }

    $adapter = TelemetryReceiver::findOne($this->request['id']);
    if (!$adapter) {
        throw new NotFoundHttpException('Адаптер не найден');
    }

    return $adapter->toArray();
}


    public function actionRead()
    {
        if (!\Yii::$app->user->can('corm_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $server = $this->getServerOr404($this->request['server_id']);

        return TelemetryReceiver::find()
            ->select(['id', 'name', 'address', 'sw_shared'])
            ->where(['server_id' => $server->id])
            ->orderBy('name')
            ->asArray()
            ->all();
    }

    public function actionGet()
{
    if (!\Yii::$app->user->can('corm_edit')) {
        throw new ForbiddenHttpException('Access denied');
    }

    $item = TelemetryReceiver::findOne($this->request['id']);
    if ($item === null) {
        throw new HttpException(404, 'Адаптер не найден');
    }

    return $item->toArray();
}


public function actionUpdate()
{
    if (!\Yii::$app->user->can('corm_edit')) {
        throw new ForbiddenHttpException('Access denied');
    }

    $request = Yii::$app->request->post();

    if (!isset($request['id'])) {
        throw new HttpException(400, 'Missing adapter ID');
    }

    $adapter = TelemetryReceiver::findOne($request['id']);
    if ($adapter === null) {
        throw new \yii\web\NotFoundHttpException('Адаптер не найден.');
    }

    $adapter->name = $request['name'];
    $adapter->address = $request['address'];
    $adapter->sw_shared = $request['sw_shared'];

    if ($adapter->save()) {
        return ['success' => true, 'adapter' => $adapter->toArray()];
    } else {
        throw new \yii\web\ServerErrorHttpException('Ошибка при обновлении адаптера.');
    }
}


public function actionSave()
{
    $request = Yii::$app->request->post();

    if (isset($request['id'])) {
        $adapter = TelemetryReceiver::findOne($request['id']);
        if ($adapter === null) {
            throw new \yii\web\NotFoundHttpException('Адаптер не найден.');
        }
    } else {
        $adapter = new TelemetryReceiver();
        $adapter->server_id = $request['server_id'];
    }

    $adapter->name = $request['name'];
    $adapter->address = $request['address'];
    $adapter->sw_shared = $request['sw_shared'];

    if ($adapter->save()) {
        return ['success' => true, 'adapter' => $adapter->toArray()];
    } else {
        throw new \yii\web\ServerErrorHttpException('Ошибка при сохранении адаптера.');
    }
}


    public function actionDelete()
    {
        if (!\Yii::$app->user->can('corm_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $item = TelemetryReceiver::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'Адаптер не найден');
        }

        $item->delete();
    }
}