<?php

namespace app\controllers\json;

use app\models\Server;
use app\classes\JsonController;
use app\models\Trunk;

class PbxController extends JsonController
{
    public function actionRead()
    {
        if (empty($this->request['servers'])) {
            return [];
        }
        
        $servers = $this->request['servers'];
        
        $serversFormatted = [];
        
        foreach ($servers as $server) {
            if ($server['id']) {
                $serversFormatted[] = $server['id'];
            }
        }
        
        if (empty($serversFormatted)) {
            return [];
        }
        
        return
            Trunk::find()
                ->select(['id_pbx as id', 'id_pbx as name'])
                ->distinct()
                ->where('server_id in (' . implode(',', $serversFormatted) . ')')
                ->andWhere('id_pbx is not null')
                ->asArray()
                ->all();
    }
}
