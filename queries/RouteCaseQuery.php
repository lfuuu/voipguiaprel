<?php
namespace app\queries;

use app\models\ConfigVersion;
use app\models\RouteCase;
use yii\db\ActiveQuery;

/**
 * @method RouteCase[] all($db = null)
 * @property
 */
class RouteCaseQuery extends ActiveQuery
{
    public function configVersion(ConfigVersion $version)
    {
        return $this->andWhere(['config_version_id' => $version->id]);
    }
}