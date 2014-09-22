<?php
namespace app\queries;

use app\models\ConfigVersion;
use app\models\RouteTable;
use yii\db\ActiveQuery;

/**
 * @method RouteTable[] all($db = null)
 * @property
 */
class RouteTableQuery extends ActiveQuery
{
    public function configVersion(ConfigVersion $version)
    {
        return $this->andWhere(['config_version_id' => $version->id]);
    }
}