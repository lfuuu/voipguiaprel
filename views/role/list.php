<?php
use yii\helpers\Url;
use yii\helpers\Html;
/**
 * @var app\components\View $this
 */
?>

<div>
    <?php if (\Yii::$app->user->can('role_create')) { ?>
    <div class="pull-right" style="display: inline-block">
        <?= Html::beginForm(['role/create'], 'get') ?>
        <button type="submit" class="btn btn-primary btn-sm">Создать</button>
        <?= Html::endForm() ?>
    </div>
    <?php } ?>
    <h4>Роли</h4>
</div>

<table class="table table-striped table-hover table-condensed" >
    <thead>
    <tr>
        <th width="50%">Название</th>
        <th width="50%">Примечание</th>
        <?php if (\Yii::$app->user->can('role_delete')) { ?><th></th><?php } ?>
    </tr>
    </thead>
    <tbody>

    <?php foreach ($roles as $item): ?>
        <tr>
            <td style="cursor: pointer" <?php if (\Yii::$app->user->can('role_edit')) { ?> onclick="location.href='<?= Url::toRoute(['role/edit', 'id' => $item->name]); ?>'" <?php } ?>>
                <?= $item->name ?>
            </td>
            <td style="cursor: pointer" <?php if (\Yii::$app->user->can('role_edit')) { ?> onclick="location.href='<?= Url::toRoute(['role/edit', 'id' => $item->name]); ?>'" <?php } ?>>
                <?= $item->description ?>
            </td>
            <?php if (\Yii::$app->user->can('role_delete')) { ?>
            <td>
                <?= Html::beginForm(['role/delete', 'name' => $item->name]) ?>
              <button type="submit" class="btn btn-danger btn-sm glyphicon glyphicon-minus"></button>
                <?= Html::endForm() ?>
            </td>
            <?php } ?>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>
