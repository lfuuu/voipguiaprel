<?php

use app\commands\RbacController;
?>
<script>
    var dataServer = <?= json_encode($this->server->toArray(), JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
    var userName = <?= json_encode(Yii::$app->user->identity->name, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
    var userId = <?= json_encode(Yii::$app->user->identity->getId(), JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
    <?php
    $userPermissions = Yii::$app->authManager->getPermissionsByUser(Yii::$app->user->identity->getId());
    $smsPermissions = RbacController::getSmsPermissions();
    $shortUserPermissions = [];
    foreach ($userPermissions as $permissionKey => $permission) {
        $shortUserPermissions[$permissionKey] = true;
    }
    ?>
    var userPermissions = <?= json_encode($shortUserPermissions, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
    var smsPermissions = <?= json_encode($smsPermissions, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
    var query = <?= json_encode($_SERVER['QUERY_STRING'], JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
    var host = <?= json_encode($_SERVER['HTTP_HOST'], JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
</script>
<div ng-controller="MainSmsCtrl" ng-include="'/templates/main_sms.html'"></div>