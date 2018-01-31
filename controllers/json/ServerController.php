<?php

namespace app\controllers\json;

use app\models\event\Queue;
use app\models\Server;
use app\classes\JsonController;

class ServerController extends JsonController
{
    public function actionList()
    {
        return
            Server::find()
                ->select(['id', 'concat(name, \' (\', id, \')\') as name'])
                ->orderBy('id')
                ->asArray()
                ->all();
    }
    
    public function actionCheckSyncProgress()
    {
        $server = $this->getServerOr404($this->request['server_id']);
    
        if (!$server->hub_id) {
            $where = "server_id = " . $this->request['server_id'];
        } else {
            $where = "(server_id in (select id from public.server where hub_id = " . $this->hub_id . ")) or server_id = " . $this->request['server_id'];
        }
    
        $result = Queue::find()
            ->where($where)
            ->exists();
    
        return $result;
    }
}
