<?php
namespace app\queries;

use app\models\ConfigVersion;
use app\models\Server;
use yii\db\ActiveQuery;

/**
 * @method ConfigVersion[] all($db = null)
 * @property
 */
class ConfigVersionQuery extends ActiveQuery
{
    public function server(Server $server)
    {
        return $this->andWhere(['server_id' => $server->id]);
    }

    public function serverId($serverId)
    {
        return $this->andWhere(['server_id' => $serverId]);
    }

    public function active()
    {
        return $this->andWhere(['status_id' => ConfigVersion::STATUS_ACTIVE]);
    }

    public function fixed()
    {
        return $this->andWhere(['status_id' => ConfigVersion::STATUS_PUBLISHED]);
    }

    public function draft()
    {
        return $this->andWhere(['status_id' => ConfigVersion::STATUS_DRAFT]);
    }

}