<?php
use app\commands\RbacController;
?>
<script>
                var dataServer = <?= json_encode($this->server->toArray(), JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
                var userName = <?= json_encode(Yii::$app->user->identity->name, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
                var userId = <?= json_encode(Yii::$app->user->identity->getId(), JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
<?php
    $userPermissions = Yii::$app->authManager->getPermissionsByUser(Yii::$app->user->identity->getId());
    $billingPermissions = RbacController::getBillingPermissions();
    $routingPermissions = RbacController::getRoutingPermissions();
    $shortUserPermissions = [];
    $billingEnabled = false;
    $routingEnabled = false;
    foreach ($userPermissions as $permissionKey => $permission) {
        $shortUserPermissions[$permissionKey] = true;

        if (!$billingEnabled && in_array($permissionKey, $billingPermissions)) {
            $billingEnabled = true;
        }

        if (!$routingEnabled && in_array($permissionKey, $routingPermissions)) {
            $routingEnabled = true;
        }
    }
?>
                var userPermissions = <?= json_encode($shortUserPermissions, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
                var billingEnabled = <?= json_encode($billingEnabled, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
                var routingEnabled = <?= json_encode($routingEnabled, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
            </script>
            <div ng-controller="MainRoutingCtrl" ng-include="'/templates/main_routing.html'"></div>
