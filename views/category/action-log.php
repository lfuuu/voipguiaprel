<?php
use yii\helpers\Html;
use app\assets\AppAsset;
use app\assets\AppLibAsset;

/**
 * @var \app\components\View $this
 * @var string $content
 */
AppLibAsset::register($this);
AppAsset::register($this);
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
<div ng-controller="MainActionLogCtrl" ng-include="'/templates/main_action_log.html'">
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
