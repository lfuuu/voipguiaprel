<?php
use yii\helpers\Url;
/**
 * @var app\components\View $this
 */
?>

<div>
    <h4>Права доступа</h4>
</div>

<table class="table table-striped table-hover table-condensed" >
    <thead>
    <tr>
        <th width="50%">Название</th>
        <th width="50%">Примечание</th>
    </tr>
    </thead>
    <tbody>

    <?php foreach ($acl_list as $item): ?>
        <tr>
            <td style="cursor: pointer">
                <?= $item->name ?>
            </td>
            <td style="cursor: pointer">
                <?= $item->description ?>
            </td>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>
