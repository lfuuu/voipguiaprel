<?php
namespace app\queries;

use app\models\ConfigVersion;
use app\models\Cpc;
use yii\db\ActiveQuery;

/**
 * @method Cpc[] all($db = null)
 * @property
 */
class CpcQuery extends ActiveQuery
{
    public function configVersion(ConfigVersion $version)
    {
        return $this->andWhere(['config_version_id' => $version->id]);
    }
}