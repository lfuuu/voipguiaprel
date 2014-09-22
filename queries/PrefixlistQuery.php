<?php
namespace app\queries;

use app\models\ConfigVersion;
use app\models\Prefixlist;
use yii\db\ActiveQuery;

/**
 * @method Prefixlist[] all($db = null)
 * @property
 */
class PrefixlistQuery extends ActiveQuery
{
    public function configVersion(ConfigVersion $version)
    {
        return $this->andWhere(['config_version_id' => $version->id]);
    }
}