<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\models\smsc_cdr\SmscCdr;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\db\Expression;

class SmsCdrController extends JsonController
{
    /**
     * Чтение списка записей из таблицы smsc_cdr.
     *
     * Фильтруем по полям a_number, b_number и setup_time (абсолютное или относительное время).
     *
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionRead()
{
    $callId          = $this->request['call_id'];
    $aNumber         = $this->request['a_number'];
    $bNumber         = $this->request['b_number'];
    $serverId        = $this->request['server_id'];
    $proto           = $this->request['proto'];
    $disconnectCause = $this->request['disconnect_cause'];
    $isTimeAbsolute  = $this->request['is_time_absolute'];
    $timeFrom        = $this->request['time_from'];
    $timeTo          = $this->request['time_to'];
    $timeRelative    = $this->request['time_relative'];
    $limit           = $this->request['limit'];

    $where = [];
    if ($callId) {
        $where['s.call_id'] = $callId;
    }
    if ($aNumber) {
        $where['s.a_number'] = $aNumber;
    }
    if ($bNumber) {
        $where['s.b_number'] = $bNumber;
    }
    if ($serverId) {
        $where['s.server_id'] = $serverId;
    }
    if ($proto) {
        $where['s.proto'] = $proto;
    }
    if ($disconnectCause) {
        $where['s.disconnect_cause'] = $disconnectCause;
    }

    // Если не задан ни один фильтр и нет временных рамок – вернуть пустой результат
    if (empty($where) && (empty($timeFrom) || empty($timeTo))) {
        return [];
    }

    if (empty($limit)) {
        $limit = 100;
    }

    $andWhere = '';
    $params   = [];
    if ($isTimeAbsolute) {
        if (empty($timeFrom) && !empty($timeTo)) {
            $andWhere = 's.setup_time <= :time_to';
            $params   = [':time_to' => $timeTo];
        } elseif (!empty($timeFrom) && empty($timeTo)) {
            $andWhere = 's.setup_time >= :time_from';
            $params   = [':time_from' => $timeFrom];
        } elseif (!empty($timeFrom) && !empty($timeTo)) {
            $andWhere = 's.setup_time >= :time_from and s.setup_time <= :time_to';
            $params   = [':time_from' => $timeFrom, ':time_to' => $timeTo];
        }
    } else {
        if (!empty($timeRelative)) {
            $andWhere = 's.setup_time >= (now() - INTERVAL \'' . (int)$timeRelative . ' seconds\') at time zone \'utc\'';
        }
    }

    $query = SmscCdr::find()
        ->alias('s')
        ->select(['s.*'])
        ->where($where)
        ->limit($limit)
        ->orderBy('s.setup_time ' . ($isTimeAbsolute ? 'ASC' : 'DESC'));

    if ($andWhere && $params) {
        $query->andWhere($andWhere)->addParams($params);
    } elseif ($andWhere) {
        $query->andWhere($andWhere);
    }

    return $query->asArray()->all();
}


    /**
     * Получение детальной информации по записи по call_id.
     *
     * @return array
     * @throws ForbiddenHttpException
     * @throws HttpException
     */
    public function actionGet()
    {
        $callId = $this->request['call_id'];
        if (!$callId) {
            throw new HttpException(400, 'Отсутствует параметр call_id');
        }

        $item = SmscCdr::find()
            ->alias('s')
            ->select(['s.*'])
            ->where(['s.call_id' => $callId])
            ->asArray()
            ->one();

        if (!$item) {
            throw new HttpException(404, 'Запись не найдена');
        }

        return $item;
    }

  

    /**
     * Возвращает уникальные значения disconnect_cause
     */
    public function actionDcOptions()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $options = SmscCdr::find()
            ->select('disconnect_cause')
            ->distinct()
            ->asArray()
            ->all();
        return array_map(function($item) {
            return $item['disconnect_cause'];
        }, $options);
    }

    /**
     * Возвращает уникальные значения proto
     */
    public function actionProtoOptions()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $options = SmscCdr::find()
            ->select('proto')
            ->distinct()
            ->asArray()
            ->all();
        return array_map(function($item) {
            return $item['proto'];
        }, $options);
    }
}
