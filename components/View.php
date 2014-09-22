<?php
namespace app\components;

use \app\models\Server;
use \app\models\ConfigVersion;

class View extends \yii\web\View
{
    /**
     * @var Server
     */
    public $server;

    /**
     * @var ConfigVersion
     */
    public $version;
}