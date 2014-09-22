<?php
namespace app\queries;

use app\models\ConfigVersion;
use app\models\Trunk;
use yii\db\ActiveQuery;

/**
 * @method Trunk[] all($db = null)
 * @property
 */
class TrunkQuery extends ActiveQuery
{
    public function configVersion(ConfigVersion $version)
    {
        return $this->andWhere(['config_version_id' => $version->id]);
    }
}