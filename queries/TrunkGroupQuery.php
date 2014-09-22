<?php
namespace app\queries;

use app\models\ConfigVersion;
use app\models\TrunkGroup;
use yii\db\ActiveQuery;

/**
 * @method TrunkGroup[] all($db = null)
 * @property
 */
class TrunkGroupQuery extends ActiveQuery
{
    public function configVersion(ConfigVersion $version)
    {
        return $this->andWhere(['config_version_id' => $version->id]);
    }
}