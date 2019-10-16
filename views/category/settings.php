<?php
use yii\helpers\Html;
use app\assets\AppSettingsAsset;
use app\assets\AppLibAsset;

AppLibAsset::register($this);
AppSettingsAsset::register($this);
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
<script>
    var userName = <?= json_encode(Yii::$app->user->identity->name, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
    var userId = <?= json_encode(Yii::$app->user->identity->getId(), JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
    <?php
        $userPermissions = Yii::$app->authManager->getPermissionsByUser(Yii::$app->user->identity->getId());
        $shortUserPermissions = [];
        foreach ($userPermissions as $permissionKey => $permission) {
            $shortUserPermissions[$permissionKey] = true;
        }
    ?>
    var userPermissions = <?= json_encode($shortUserPermissions, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
</script>
<div ng-controller="MainSettingsCtrl" ng-include="'/templates/main_settings.html'">
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
