<?php
use app\models\Hub;
use app\models\Server;
use yii\helpers\Url;

/**
 * @var app\components\View $this
 * @var Server[] $servers
 * @var Hub[] $hubs
 */
?>

<table class="table table-striped table-hover table-condensed" >
    <thead>
    <tr>
        <th style="width:20%">Код</th>
        <th style="width:20%">Название</th>
        <th style="width:20%">Название короткое</th>
        <th style="width:35%">Флаги</th>
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
            <td style="cursor: pointer" onclick="location.href='<?= Url::toRoute(['server/index', 'serverId' => $item->id]); ?>'">
                <?= $item->name_short ?>
            </td>
            <td>
               <?php if(!$item->instanceSettings->active) { ?>
                  <span class="label label-warning">Не активен</span>
               <?php } ?>
               <?php if($item->instanceSettings->is_can_recalculate) { ?>
                  <span class="label label-info">Пересчитывать</span>
               <?php } ?>
               <?php if($item->is_need_db_do_migrate) { ?>
                  <span class="label label-success">Мигрировать БД</span>
               <?php } ?>
               <?php if($item->is_sormed) { ?>
                  <span class="label label-warning">СОРМ</span>
               <?php } ?>
                <?php if(!$item->instanceSettings->auto_lock_finance) { ?>
                    <span class="label label-warning">Финансовая автоблокировка выкл</span>
                <?php } ?>
               <?php if($item->is_production) { ?>
                  <span class="label label-danger">В коммерции</span>
               <?php } ?>
            </td>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>


<?php foreach ($hubs as $item): ?>
    <table class="table table-striped table-hover table-condensed" >
        <thead>
        <tr>
            <th style="width:20%">Код</th>
            <th style="width:20%">Название</th>
            <th style="width:20%">Название короткое</th>
            <th style="width:35%">Флаги</th>
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
                    <td style="cursor: pointer" onclick="location.href='<?= Url::toRoute(['server/index', 'serverId' => $item2->id]); ?>'">
                        <?= $item2->name_short ?>
                    </td>
                    <td>
                       <?php if(!$item2->instanceSettings->active) { ?>
                          <span class="label label-warning">Не активен</span>
                       <?php } ?>
                       <?php if(!$item2->instanceSettings->is_can_recalculate) { ?>
                          <span class="label label-info">Не пересчитывать</span>
                       <?php } ?>
                       <?php if(!$item2->is_need_db_do_migrate) { ?>
                          <span class="label label-success">Не мигрировать БД</span>
                       <?php } ?>
                       <?php if($item2->is_sormed) { ?>
                        <span class="label label-warning">СОРМ</span>
                       <?php } ?>
                       <?php if(!$item2->instanceSettings->auto_lock_finance) { ?>
                         <span class="label label-warning">Финансовая автоблокировка выкл</span>
                       <?php } ?>
                       <?php if(!empty($item2->is_production)) { ?>
                         <span class="label label-danger">В коммерции</span>
                       <?php } ?>
                    </td>
                </tr>
            <?php endforeach; ?>

        </tbody>
    </table>
<?php endforeach; ?>
