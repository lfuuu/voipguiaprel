<?php
use app\commands\RbacController;
use yii\helpers\Json;

/** @var \app\components\View $this */
/** @var app\models\ServerOcs[] $servers */

$userPerms = Yii::$app->authManager
    ->getPermissionsByUser(Yii::$app->user->identity->getId());
$shortUserPerms = [];
foreach ($userPerms as $k => $p) {
    $shortUserPerms[$k] = true;
}

$firstServer = !empty($servers) ? reset($servers) : null;
?>
<script>
  var userName         = <?= Json::encode(Yii::$app->user->identity->name,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
  var userId           = <?= Json::encode(Yii::$app->user->identity->getId(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
  var userPermissions  = <?= Json::encode($shortUserPerms,                 JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
  var billingPermissions = <?= Json::encode(RbacController::getBillingPermissions(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
  var smsPermissions   = <?= Json::encode(RbacController::getSmsPermissions(),      JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
  var query            = <?= Json::encode($_SERVER['QUERY_STRING'],         JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
  var host             = <?= Json::encode($_SERVER['HTTP_HOST'],           JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;

  <?php if ($firstServer): ?>
  var dataServer = {
      id:   <?= Json::encode($firstServer->id,   JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>,
      name: <?= Json::encode($firstServer->name, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>
  };
  <?php endif; ?>
</script>

<div ng-controller="MainSmsCtrl"
     ng-init="selectServer(dataServer)"
     ng-include="'templates/main_sms.html'">
</div>
