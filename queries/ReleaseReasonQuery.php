<?php
namespace app\queries;

use app\models\ConfigVersion;
use app\models\ReleaseReason;
use yii\db\ActiveQuery;

/**
 * @method ReleaseReason[] all($db = null)
 * @property
 */
class ReleaseReasonQuery extends ActiveQuery
{
    public function configVersion(ConfigVersion $version)
    {
        return $this->andWhere(['config_version_id' => $version->id]);
    }
}