<?php
use yii\helpers\Html;
use yii\helpers\Url;
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
    <div class="wrap">
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
            <div class="container-fluid">
                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <?php if (!Yii::$app->user->isGuest): ?>
                    <ul class="nav navbar-nav navbar-left">
                        <li<?php if ($_SERVER['REQUEST_URI'] == '/' || preg_match("/^[\/][sS][\d]{1,3}$/", $_SERVER['REQUEST_URI'])) { ?> style="text-decoration: underline;" <?php } ?>><a href="<?=Url::to(['site/index'])?>">Маршрутизация</a></li>
                        <li<?php if ($_SERVER['REQUEST_URI'] == '/billing') { ?> style="text-decoration: underline;" <?php } ?>><a href="<?=Url::to(['category/billing'])?>">Билингация</a></li>
                        <?php if (\Yii::$app->user->can('marketplace_list') || \Yii::$app->user->can('marketplace_edit')): ?>
                        <li<?php if ($_SERVER['REQUEST_URI'] == '/marketplace') { ?> style="text-decoration: underline;" <?php } ?>><a href="<?=Url::to(['category/marketplace'])?>">Биржа РФ</a></li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('marketplace_list') || \Yii::$app->user->can('marketplace_edit')): ?>
                            <li<?php if ($_SERVER['REQUEST_URI'] == '/marketplace-eu') { ?> style="text-decoration: underline;" <?php } ?>><a href="<?=Url::to(['category/marketplace-eu'])?>">Биржа EU</a></li>
                        <?php endif; ?>
                        <li><a href="<?=Url::to(['/health/health.html'])?>" target="_blank">Здоровье биллеров</a></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <?php if (\Yii::$app->user->can('acl_list')) { ?>
                        <li><a href="<?=Url::to(['acl/list'])?>">Права доступа</a></li>
                        <?php } ?>
                        <?php if (\Yii::$app->user->can('role_list')) { ?>
                        <li><a href="<?=Url::to(['role/list'])?>">Роли</a></li>
                        <?php } ?>
                        <?php if (\Yii::$app->user->can('user_list')) { ?>
                        <li><a href="<?=Url::to(['user/list'])?>">Пользователи</a></li>
                        <?php } ?>
                        <li><a><?= Yii::$app->user->identity->name ?></a></li>
                        <li><a href="<?=Url::to(['site/logout'])?>">Выход</a></li>
                    </ul>
                    <?php endif; ?>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
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
