<?php
namespace app\queries;

use app\models\ConfigVersion;
use app\models\Operator;
use yii\db\ActiveQuery;

/**
 * @method Operator[] all($db = null)
 * @property
 */
class OperatorQuery extends ActiveQuery
{
    public function configVersion(ConfigVersion $version)
    {
        return $this->andWhere(['config_version_id' => $version->id]);
    }
}