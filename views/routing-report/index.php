<?php
/** @var $operators \app\models\Operator[] */
?>
<style type="text/css">
    tr td.best {
        background-color: #99F799 ! important;
    }
    tr td.locked {
        background-color: #ff8888 ! important;
    }
</style>
<br/>
<table class="table table-bordered table-striped table-condensed">
    <tr>
        <th nowrap style="min-width: 100px;">Префикс номера</th>
        <th style="min-width: 100px;">Назначение</th>
        <th nowrap style="min-width: 100px;text-align: center">Первая цена</th>
        <?php foreach ($operators as $operator): ?>
        <th nowrap align=center style="min-width: 100px;text-align: center"><?=$operator->name?></th>
        <?php endforeach ?>
        <th nowrap style="min-width: 100px;text-align: center">Порядок</th>
    </tr>
    <?php foreach ($report as $rd): ?>
    <tr>
        <td><?=$rd['prefix']?></td>
        <td><?=$rd['destination']?><?=$rd['mob']=='t' ? ' (моб.)' : ''?></td>
        <td nowrap align=center style="font-weight: bold"><?=str_replace('.',',',$rd['best_price'])?></td>
        <?php $i = 0; ?>
        <?php foreach ($operators as $operator): ?>
            <td nowrap align=center class="<?= isset($rd['orders'][0]) && $rd['orders'][0] == $i ? 'best' : ''?> <?= $rd['locks'][$i] == 't' ? 'locked' : ''?>">
                <?= $rd['prices'][$i] != 'NULL' ? str_replace('.',',',$rd['prices'][$i]) : '' ?>
            </td>
            <?php $i++; ?>
        <?php endforeach ?>
        <td nowrap align=center>
            <?php $i = 0; ?>
            <?php foreach ($rd['routes'] as $operatorId): ?>
                <?=$i > 0 ? '->' : ''?>
                <?= $operators[$operatorId]->name ?>
                <?php $i++; ?>
            <?php endforeach ?>
        </td>
    </tr>
    <?php endforeach ?>
</table>
