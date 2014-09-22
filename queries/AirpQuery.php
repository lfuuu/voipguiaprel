<?php
namespace app\queries;

use app\models\ConfigVersion;
use yii\db\ActiveQuery;
use app\models\Airp;

/**
 * @method Airp[] all($db = null)
 * @property
 */
class AirpQuery extends ActiveQuery
{
    public function configVersion(ConfigVersion $version)
    {
        return $this->andWhere(['config_version_id' => $version->id]);
    }
}