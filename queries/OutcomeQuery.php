<?php
namespace app\queries;

use app\models\ConfigVersion;
use app\models\Outcome;
use yii\db\ActiveQuery;

/**
 * @method Outcome[] all($db = null)
 * @property
 */
class OutcomeQuery extends ActiveQuery
{
    public function configVersion(ConfigVersion $version)
    {
        return $this->andWhere(['config_version_id' => $version->id]);
    }
}