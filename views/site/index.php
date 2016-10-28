<?php
use yii\helpers\Url;
/**
 * @var app\components\View $this
 */
?>

<div>
    <a class="btn btn-primary btn-sm pull-right" ng-click="clickCreate()">Создать</a>
    <h4>Сервера</h4>
</div>

<table class="table table-striped table-hover table-condensed" >
    <thead>
    <tr>
        <th style="width:20%">Код</th>
        <th>Название</th>
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
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>


<?php foreach ($hubs as $item): ?>

<div>
    <a class="btn btn-primary btn-sm pull-right" ng-click="clickCreate()">Присоеденить</a>
    <h4><?= $item->name ?></h4>
</div>


<table class="table table-striped table-hover table-condensed" >
    <thead>
    <tr>
        <th style="width:20%"style="width:20%">Код</th>
        <th>Название</th>
    </tr>
    </thead>
    <tbody>

    <?php foreach($item->servers as $item2): ?>
        <tr>
            <td style="cursor: pointer" onclick="location.href='<?= Url::toRoute(['server/index', 'serverId' => $item2->id]); ?>'">
                <?= $item2->id ?>
            </td>
            <td style="cursor: pointer" onclick="location.href='<?= Url::toRoute(['server/index', 'serverId' => $item2->id]); ?>'">
                <?= $item2->name ?>
            </td>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>

<?php endforeach; ?>
