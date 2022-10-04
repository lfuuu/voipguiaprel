<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\AppAsset;
use app\assets\AppSmsAsset;
use app\assets\AppSettingsAsset;
use app\assets\AppCamelAsset;
use app\assets\AppApiBillingAsset;
use app\assets\AppLibAsset;
use app\commands\RbacController;
use Yii;

/**
 * @var \app\components\View $this
 * @var string $content
 */

$isEu = Yii::$app->params['isEuropean'];

AppLibAsset::register($this);
if ($_SERVER['REQUEST_URI'] == '/routing') {
    AppAsset::register($this);
} elseif ($_SERVER['REQUEST_URI'] == '/sms' || preg_match("/^\/ms/i", $_SERVER['REQUEST_URI'])){
    AppSmsAsset::register($this);
} elseif ($_SERVER['REQUEST_URI'] == '/billing') {
    AppAsset::register($this);
} elseif ($_SERVER['REQUEST_URI'] == '/camel' || preg_match("/^\/cs/i", $_SERVER['REQUEST_URI'])) {
    AppCamelAsset::register($this);
} elseif ($_SERVER['REQUEST_URI'] == '/settings') {
    AppSettingsAsset::register($this);
} elseif ($_SERVER['REQUEST_URI'] == '/api-billing' || preg_match("/^\/abs/i", $_SERVER['REQUEST_URI'])) {
    AppApiBillingAsset::register($this);
} else {
    AppAsset::register($this);
}

try {
    $handle = fopen("../.helm/def.sh", "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            if (strpos($line, 'TAG=') !== false) {
                $version = trim(substr($line, 4));
                break;
            }
        }
        fclose($handle);
    } else {
        $version = 1;
    }
} catch (\Exception $e) {
    $version = 2;
}

if (Yii::$app->user->identity) {
    $userPermissions = Yii::$app->authManager->getPermissionsByUser(Yii::$app->user->identity->getId());

    $routingPermissions = RbacController::getRoutingPermissions();
    $billingPermissions = RbacController::getBillingPermissions();
    $camelPermissions = RbacController::getCamelPermissions();
    $settingsPermissions = RbacController::getSettingsPermissions();
    $apiBillingPermissions = RbacController::getApiBillingPermissions();
    $smsPermissions = RbacController::getSmsPermissions();

    $userRoutingPermissions = [];
    $userBillingPermissions = [];
    $userCamelPermissions = [];
    $userApiBillingPermissions = [];
    $userSettingsPermissions = [];
    $userSmsPermissions = [];

    foreach ($userPermissions as $permissionKey => $permission) {
        if (in_array($permission->name, $routingPermissions)) {
            $userRoutingPermissions[$permissionKey] = true;
        } else if (in_array($permission->name, $billingPermissions)) {
            $userBillingPermissions[$permissionKey] = true;
        } else if (in_array($permission->name, $camelPermissions)) {
            $userCamelPermissions[$permissionKey] = true;
        } else if (in_array($permission->name, $settingsPermissions)) {
            $userSettingsPermissions[$permissionKey] = true;
        } else if (in_array($permission->name, $apiBillingPermissions)) {
            $userApiBillingPermissions[$permissionKey] = true;
        } else if (in_array($permission->name, $smsPermissions)) {
            $userSmsPermissions[$permissionKey] = true;
        }
    }

    $userHasRouting = count($userRoutingPermissions) > 0 ? true : false;
    $userHasBilling = count($userBillingPermissions) > 0 ? true : false;
    $userHasCamel = count($userCamelPermissions) > 0 ? true : false;
    $userHasSettings = count($userSettingsPermissions) > 0 ? true : false;
    $userHasApiBilling = count($userApiBillingPermissions) > 0 ? true : false;
    $userHasSms = count($userSmsPermissions) > 0 ? true : false;
} else {
    $userHasRouting = false;
    $userHasBilling = false;
    $userHasCamel = false;
    $userHasSettings = false;
    $userHasApiBilling = false;
    $userHasSms = false;
}

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" ng-app="app">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

</head>
<body>
<?php $this->beginBody() ?>
    <div class="wrap">
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
            <div class="container-fluid">
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    
<?php if (!Yii::$app->user->isGuest): ?>
                    <ul class="nav navbar-nav navbar-left">
<?php if ($isEu) { ?>
                        <li><img src="./logos/logo-eu.svg" width="100" height="50"></li>
<?php } else {?>
                        <li><img src="./logos/logo.svg" width="100" height="50"></li>
<?php } ?>
<?php if ($userHasRouting) { ?>
                        <li<?php if ($_SERVER['REQUEST_URI'] == '/' || preg_match("/^[\/][sS][\d]{1,3}$/", $_SERVER['REQUEST_URI'])) { ?> style="text-decoration: underline;" <?php } ?>><a href="<?=Url::to(['site/index'])?>">Маршрутизация</a></li>
<?php } ?>
<?php if ($userHasBilling) { ?>
                        <li<?php if ($_SERVER['REQUEST_URI'] == '/billing') { ?> style="text-decoration: underline;" <?php } ?>><a href="<?=Url::to(['category/billing'])?>">Билингация</a></li>
<?php } ?>
<?php if ($userHasCamel && !$isEu) { ?>
                        <li<?php if ($_SERVER['REQUEST_URI'] == '/camel'|| preg_match("/^\/cs/i", $_SERVER['REQUEST_URI'])) { ?> style="text-decoration: underline;" <?php } ?>><a href="<?=Url::to(['category/camel'])?>">Camel</a></li>
<?php } ?>
<?php if ($userHasApiBilling) { ?>
                        <li<?php if ($_SERVER['REQUEST_URI'] == '/api-billing'|| preg_match("/^\/abs/i", $_SERVER['REQUEST_URI'])) { ?> style="text-decoration: underline;" <?php } ?>><a href="<?=Url::to(['category/api-billing'])?>">Биллинг API</a></li>
<?php } ?>
<?php if ((\Yii::$app->user->can('marketplace_list') || \Yii::$app->user->can('marketplace_edit')) && !$isEu): ?>
                        <li<?php if ($_SERVER['REQUEST_URI'] == '/marketplace') { ?> style="text-decoration: underline;" <?php } ?>><a href="<?=Url::to(['category/marketplace'])?>">Биржа РФ</a></li>
<?php endif; ?>
<?php if ((\Yii::$app->user->can('marketplace_list') || \Yii::$app->user->can('marketplace_edit')) && $isEu): ?>
                        <li<?php if ($_SERVER['REQUEST_URI'] == '/marketplace-eu') { ?> style="text-decoration: underline;" <?php } ?>><a href="<?=Url::to(['category/marketplace-eu'])?>">Биржа EU</a></li>
<?php endif; ?>
                        <li><a href="<?=Url::to(['/health/health.html'])?>" target="_blank">Здоровье биллеров</a></li>
<?php if ($userHasSms) { ?>
                        <li<?php if ($_SERVER['REQUEST_URI'] == '/sms') { ?> style="text-decoration: underline;" <?php } ?>><a href="<?=Url::to(['category/sms'])?>">SMS</a></li>
<?php } ?>

                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li><a>Версия: <?= $version ?></a></li>
<?php if ($userHasSettings) { ?>
                        <li<?php if ($_SERVER['REQUEST_URI'] == '/settings') { ?> style="text-decoration: underline;" <?php } ?>><a href="<?=Url::to(['category/settings'])?>">Настройки</a></li>
<?php } ?>
                        <li><a><?= Yii::$app->user->identity->name ?></a></li>
                        <li><a href="<?=Url::to(['site/logout'])?>">Выход</a></li>
                    </ul>
<?php endif; ?>
                </div>
            </div>
        </nav>
        <div style="position: fixed; overflow: auto; bottom: 0; right: 0; top: 60px; padding-left: 20px; padding-right: 20px; left: 0;">
            <div class="alert alert-danger" ng-cloak ng-repeat="error in errors">
                <p ng-repeat="err in error">{{err}}</p>
            </div>
<?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success">
                    <?php echo Yii::$app->session->getFlash('success', null, true); ?>
                </div>
<?php endif ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger">
                    <?php echo Yii::$app->session->getFlash('error'); ?>
                </div>
<?php endif; ?>
            <?= $content ?>
        </div>
    </div>
<?php $this->endBody() ?>

</body>
</html>
<?php $this->endPage() ?>