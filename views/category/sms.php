<?php

use app\models\ServerOcs;
use yii\helpers\Url;

/**
 * @var app\components\View $this
 * @var ServerOcs[] $servers
 */
?>

<table class="table table-striped table-hover table-condensed">
    <thead>
        <tr>
            <th style="width:20%">Код</th>
            <th style="width:20%">Название</th>
        </tr>
    </thead>
        <tr>
            <td style="cursor: pointer" onclick="location.href='<?= Url::toRoute(['sms/index', 'serverId' => $servers->id]); ?>'"><?= $servers->id ?></td>
            <td style="cursor: pointer" onclick="location.href='<?= Url::toRoute(['sms/index', 'serverId' => $servers->id]); ?>'"><?= $servers->name ?></td>
        </tr>
    </tbody>
</table>