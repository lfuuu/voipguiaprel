<?php
use app\commands\RbacController;
?>
<script>
                var userName = <?= json_encode(Yii::$app->user->identity->name, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
                var userId = <?= json_encode(Yii::$app->user->identity->getId(), JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
<?php
    $userPermissions = Yii::$app->authManager->getPermissionsByUser(Yii::$app->user->identity->getId());
    $settingsPermissions = RbacController::getSettingsPermissions();
    $shortUserPermissions = [];
    foreach ($userPermissions as $permissionKey => $permission) {
        $shortUserPermissions[$permissionKey] = true;
    }
?>
                var userPermissions = <?= json_encode($shortUserPermissions, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
                var settingsPermissions = <?= json_encode($settingsPermissions, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
                var query = <?= json_encode($_SERVER['QUERY_STRING'], JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
                var host = <?= json_encode($_SERVER['HTTP_HOST'], JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
            </script>
            <div ng-controller="MainSettingsCtrl" ng-include="'/templates/main_settings.html'"></div>
