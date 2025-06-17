<?php use app\commands\RbacController; ?>
<script>
    var userName = <?= json_encode(Yii::$app->user->identity->name, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES) ?>;
    var userId   = <?= json_encode(Yii::$app->user->identity->getId(), JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES) ?>;
    <?php
    // собираем права пользователя
    $userPermissions = Yii::$app->authManager->getPermissionsByUser(Yii::$app->user->identity->getId());
    // права биллинга
    $billingPermissions = RbacController::getBillingPermissions();
    // сокращённый массив ключей прав
    $shortUserPermissions = [];
    foreach ($userPermissions as $permissionKey => $permission) {
        $shortUserPermissions[$permissionKey] = true;
    }
    ?>
    var userPermissions    = <?= json_encode($shortUserPermissions, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES) ?>;
    var billingPermissions = <?= json_encode($billingPermissions, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES) ?>;
    var query              = <?= json_encode($_SERVER['QUERY_STRING'], JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES) ?>;
    var host               = <?= json_encode($_SERVER['HTTP_HOST'], JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES) ?>;
</script>

<div ng-controller="MainBillingServersCtrl"
     ng-include="'templates/billingservers/main_billing_servers.html'">
</div>
