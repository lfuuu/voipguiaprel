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
    
    public function actionListByHub()
    {
        $hubId = $this->request['hub_id'];
        
        if ($hubId) {
            $where = ['hub_id' => $hubId];
        } else {
            $where = 'hub_id is null';
        }
        
        $result = Server::find()
                ->select(['id', 'concat(name, \' (\', id, \')\') as name'])
                ->where($where)
                ->orderBy('id')
                ->asArray()
                ->all();
        
        return $result;
    }
    
    public function actionListByHubWithContract()
    {
        $hubId = $this->request['hub_id'];
        
        if ($hubId) {
            $where = ['hub_id' => $hubId];
        } else {
            $where = 'hub_id is null';
        }
        
        $result = Server::find()
            ->select(['server.id', 'concat(server.name, \' (\', server.id, \')\') as name'])
            ->innerJoin('auth.trunk t', 't.server_id = server.id')
            ->innerJoin('billing.service_trunk st', 'st.trunk_id = t.id')
            ->where($where)
            ->andWhere('t.our_trunk = false')
            ->andWhere('st.activation_dt < now()')
            ->andWhere('st.expire_dt > now()')
            ->andWhere('st.term_enabled = true')
            ->orderBy('id')
            ->asArray()
            ->all();
        
        return $result;
    }
    
    public function actionCheckSyncProgress()
    {
        $server = $this->getServerOr404($this->request['server_id']);
    
        if (!isset($server->hub_id)) {
            $where = "server_id = " . $this->request['server_id'];
        } else {
            $where = "(server_id in (select id from public.server where hub_id = " . $server->hub_id . ")) or server_id = " . $this->request['server_id'];
        }
    
        $result = Queue::find()
            ->where($where)
            ->exists();
    
        return $result;
    }
}
