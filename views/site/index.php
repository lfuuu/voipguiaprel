<?php
use app\models\auth\Hub;
use app\models\Server;
use yii\helpers\Url;
use Yii;
/**
 * @var app\components\View $this
 * @var Server[] $servers
 * @var Hub[] $hubs
 */

$is_eu = Yii::$app->params['isEuropean'];
?>


<?php foreach ($hubs as $item): ?>
    <?php 
        if ($is_eu && $item->market_place_id == Hub::EUROPEAN_HUB) {
            $euItems[] = $item;
        } elseif (!$is_eu && $item->market_place_id == Hub::RUSSIAN_HUB) {
            $items[] = $item;
        }
    ?>
<?php endforeach; ?>
<?php foreach (($is_eu ? $euItems : $items) as $item): ?>
    <?php if ((count($item->servers) == 0 || empty($item->name))) {continue;} ?>
    <h4><?= $item->name; ?></h4>
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
    <?php if ($item2->active): ?>
                            <tr>
                                <td style="cursor: pointer" onclick="location.href='<?= Url::toRoute(['server/index', 'serverId' => $item2->id]); ?>'"><?= $item2->id ?></td>
                                <td style="cursor: pointer" onclick="location.href='<?= Url::toRoute(['server/index', 'serverId' => $item2->id]); ?>'"><?= $item2->name ?></td>
                                <td style="cursor: pointer" onclick="location.href='<?= Url::toRoute(['server/index', 'serverId' => $item2->id]); ?>'"><?= $item2->name_short ?></td>
                                <td>
        <?php if (!empty($item2->preparedPrefixlists)) { ?>
                                    <span class="label label-danger">Префикслист</span>
        <?php } ?>
        <?php if (isset($item->instanceSettings) && !$item2->instanceSettings->active) { ?>
                                    <span class="label label-warning">Не активен</span>
        <?php } ?>
        <?php if (isset($item->instanceSettings) && $item2->instanceSettings->is_can_recalculate) { ?>
                                    <span class="label label-info">Пересчитывать</span>
        <?php } ?>
        <?php if ($item2->is_need_db_do_migrate) { ?>
                                    <span class="label label-success">Мигрировать БД</span>
        <?php } ?>
        <?php if ($item2->is_sormed) { ?>
                                    <span class="label label-warning">СОРМ</span>
        <?php } ?>
        <?php if (isset($item->instanceSettings) && !$item2->instanceSettings->auto_lock_finance) { ?>
                                    <span class="label label-warning">Финансовая автоблокировка выкл</span>
        <?php } ?>
        <?php if (!empty($item2->is_production)) { ?>
                                    <span class="label label-danger">В коммерции</span>
        <?php } ?>
                                </td>
                            </tr>
    <?php endif; ?>
<?php endforeach; ?>
                </tbody>
            </table>
<?php endforeach; ?>
