<?php

namespace app\controllers\json;
use app\models\Trunk;

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
    $hub_id = $server->hub_id > 0 ? $server->hub_id : 0;

    return TelemetryReceiver::find()
        ->select(['id', 'name'])
        ->where("server_id in (select id from public.server where hub_id = :hub_id) or server_id = :server_id")
        ->addParams([':hub_id' => $hub_id, ':server_id' => $server->id])
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
    $adapter->add_out_trunk = $request['add_out_trunk'];
    $adapter->del_in_trunk  = $request['del_in_trunk'];

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
    $adapter->add_out_trunk = $request['add_out_trunk'];
    $adapter->del_in_trunk  = $request['del_in_trunk'];

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

    public function actionTrunksByUs($id)
{
    if (!\Yii::$app->user->can('corm_edit')) {
        throw new ForbiddenHttpException('Access denied');
    }

    $usType = Yii::$app->request->get('usType');
    $serverId = Yii::$app->request->get('server_id');

    if ($usType === null) {
        throw new \yii\web\BadRequestHttpException('Missing usType parameter');
    }

    if ($serverId === null) {
        throw new \yii\web\BadRequestHttpException('Missing server_id parameter');
    }

    $trunks = Trunk::find()
        ->where(['sorm_p268_us_type' => $usType, 'server_id' => $serverId])
        ->orderBy('name')
        ->asArray()
        ->all();

    header('Content-Type: text/html');

    foreach ($trunks as $trunk) {
        echo $trunk['name'] . "<br/>";
    }

    exit();
}


}