<?php
use yii\helpers\Html;

/** @var \app\components\View $this */
/** @var string $content */

\app\assets\AppMinimalAsset::register($this);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>

        <?php $this->beginBody() ?>

            <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
                <div class="container-fluid">
                    <div class="collapse navbar-collapse">
                        <ul class="nav navbar-nav navbar-right">
                            <li>
                                <a href="javascript:self.close()" class="btn btn-default" style="padding: 10px; margin-top:3px; margin-right:3px;">
                                    X
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <?= $content ?>

        <?php $this->endBody() ?>

    </body>
</html>
<?php $this->endPage() ?>
