<?php
use app\commands\RbacController;
?>
<script>
                var userName = <?= json_encode(Yii::$app->user->identity->name, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
                var userId = <?= json_encode(Yii::$app->user->identity->getId(), JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
<?php
    $userPermissions = Yii::$app->authManager->getPermissionsByUser(Yii::$app->user->identity->getId());
    $billingPermissions = RbacController::getBillingPermissions();
    $shortUserPermissions = [];
    foreach ($userPermissions as $permissionKey => $permission) {
        $shortUserPermissions[$permissionKey] = true;
    }
?>
                var userPermissions = <?= json_encode($shortUserPermissions, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
                var billingPermissions = <?= json_encode($billingPermissions, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES); ?>;
            </script>
            <div ng-controller="MainMarketplaceEuCtrl" ng-include="'/templates/main_marketplace.html'"></div>
