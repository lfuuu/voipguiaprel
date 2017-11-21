<?php
use yii\helpers\Url;
use yii\helpers\Html;
/**
 * @var app\components\View $this
 */
?>

<div>
    <?php if (\Yii::$app->user->can('user_create')) { ?>
    <div class="pull-right" style="display: inline-block">
        <?= Html::beginForm(['user/create'], 'get') ?>
        <button type="submit" class="btn btn-primary btn-sm">Создать</button>
        <?= Html::endForm() ?>
    </div>
    <?php } ?>
    <h4>Пользователи</h4>
</div>

<table class="table table-striped table-hover table-condensed" >
    <thead>
    <tr>
        <th width="20%">Логин</th>
        <th width="80%">Имя</th>
        <?php if (\Yii::$app->user->can('user_delete')) { ?><th></th><?php } ?>
    </tr>
    </thead>
    <tbody>

    <?php foreach ($users as $item): ?>
        <tr>
            <td style="cursor: pointer" <?php if (\Yii::$app->user->can('user_create')) { ?> onclick="location.href='<?= Url::toRoute(['user/edit', 'id' => $item->id]); ?>'" <?php } ?>>
                <?= $item->login ?>
            </td>
            <td style="cursor: pointer" <?php if (\Yii::$app->user->can('user_create')) { ?> onclick="location.href='<?= Url::toRoute(['user/edit', 'id' => $item->id]); ?>'" <?php } ?>>
                <?= $item->name ?>
            </td>
            <?php if (\Yii::$app->user->can('user_delete')) { ?>
            <td>
                <?= Html::beginForm(['user/delete', 'id' => $item->id]) ?>
                <button type="submit" class="btn btn-danger btn-sm glyphicon glyphicon-minus"></button>
                <?= Html::endForm() ?>
            </td>
            <?php } ?>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>
