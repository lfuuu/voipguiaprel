<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\ConfigVersion;
/**
 * @var app\components\View $this
 */
?>

<div>
    <div class="pull-right" style="display: inline-block">
        <?= Html::beginForm(['server/create-config', 'serverId' => $serverId]) ?>
            <button type="submit" class="btn btn-primary btn-sm">Создать</button>
        <?= Html::endForm() ?>
    </div>
    <h4>Версии конфигурации</h4>
</div>

<table class="table table-striped table-hover table-condensed" >
    <thead>
    <tr>
        <th>Название</th>
        <th colspan="2">Статус</th>
        <th>Дата изменения</th>
        <th>Дата активации</th>
        <th></th>
        <th width="1%"></th>
    </tr>
    </thead>
    <tbody>

    <?php foreach ($versions as $item): ?>
        <tr>
            <td style="cursor: pointer" onclick="location.href='<?= Url::toRoute(['config/index', 'versionId' => $item->id]); ?>'">
                <?= $item->name ?>
            </td>
            <td style="cursor: pointer" onclick="location.href='<?= Url::toRoute(['config/index', 'versionId' => $item->id]); ?>'">
                <?php if ($item->status_id == ConfigVersion::STATUS_DRAFT): ?>
                    <span class="label label-default">Черновик</span>
                <?php elseif ($item->status_id == ConfigVersion::STATUS_PUBLISHED): ?>
                    <span class="label label-primary">Опубликовано</span>
                <?php elseif ($item->status_id == ConfigVersion::STATUS_ACTIVE): ?>
                    <span class="label label-success">Активный</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($item->status_id == ConfigVersion::STATUS_DRAFT) { ?>
                    <?= Html::beginForm(['config/fix', 'id' => $item->id]) ?>
                    <button type="submit" class="btn btn-default btn-xs">Зафиксировать</button>
                    <?= Html::endForm() ?>
                <?php } elseif ($item->status_id == ConfigVersion::STATUS_PUBLISHED) { ?>
                    <?= Html::beginForm(['config/activate', 'id' => $item->id]) ?>
                    <button type="submit" class="btn btn-default btn-xs">Активировать</button>
                    <?= Html::endForm() ?>
                <?php } ?>
            </td>
            <td style="cursor: pointer" onclick="location.href='<?= Url::toRoute(['config/index', 'versionId' => $item->id]); ?>'">
                <?= $item->updated_at ?>
            </td>
            <td style="cursor: pointer" onclick="location.href='<?= Url::toRoute(['config/index', 'versionId' => $item->id]); ?>'">
                <?= $item->activated_at ?>
            </td>
            <td>
                <?= Html::beginForm(['config/clone', 'id' => $item->id]) ?>
                    <button type="submit" class="btn btn-default btn-xs">Клонировать</button>
                <?= Html::endForm() ?>
            </td>
            <td>
                <? if ($item->status_id != ConfigVersion::STATUS_ACTIVE): ?>
                    <?= Html::beginForm(['config/delete', 'id' => $item->id]) ?>
                        <button type="submit" class="btn btn-danger btn-sm glyphicon glyphicon-minus"></button>
                    <?= Html::endForm() ?>
                <? endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>
