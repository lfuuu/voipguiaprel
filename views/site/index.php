<?php
use yii\helpers\Url;
/**
 * @var app\components\View $this
 */
?>

<div>
    <a class="btn btn-primary btn-sm pull-right" ng-if="version.editable" ng-click="clickCreate()">Создать</a>
    <h4>Сервера</h4>
</div>

<table class="table table-striped table-hover table-condensed" >
    <thead>
    <tr>
        <th>Код</th>
        <th>Название</th>
        <th>Активная конфигурация</th>
    </tr>
    </thead>
    <tbody>

    <?php foreach ($servers as $item): ?>
        <tr>
            <td style="cursor: pointer" onclick="location.href='<?= Url::toRoute(['server/index', 'serverId' => $item->id]); ?>'">
                <?= $item->id ?>
            </td>
            <td style="cursor: pointer" onclick="location.href='<?= Url::toRoute(['server/index', 'serverId' => $item->id]); ?>'">
                <?= $item->name ?>
            </td>
            <td>
                <? if ($config = $item->getActualConfig()): ?>
                    <a href="<?= Url::toRoute(['config/index', 'versionId' => $config->id]); ?>"><?= $config->name ?></a>
                <? endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>

