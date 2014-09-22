<?php
namespace app\queries;

use app\models\ConfigVersion;
use app\models\Number;
use yii\db\ActiveQuery;

/**
 * @method Number[] all($db = null)
 * @property
 */
class NumberQuery extends ActiveQuery
{
    public function configVersion(ConfigVersion $version)
    {
        return $this->andWhere(['config_version_id' => $version->id]);
    }
}