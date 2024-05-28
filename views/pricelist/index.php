<?php
use app\models\auth\Hub;
use app\models\Server;
use yii\helpers\Url;

/**
 * @var app\components\View $this
 * @var Server[] $servers
 * @var Hub[] $hubs
 */
?>
<?php $dateNow = date('Y-m-d'); ?>
<table class="table table-condensed">
    <tbody>
        <tr>
            <td colspan="8" style="text-align: center;">
                <?= (isset($pricelist['description']) ? ($pricelist['description'] . ', валюта ' . $pricelist['currency_id'] . ' | ') : ('Валюта ' . $pricelist['currency_id'] . ' | ') . ($pricelist['name'] . ' | ' . ($pricelist['orig'] ? 'Оригинация' : 'Терминация') . (' | Создан: ' .  $pricelist['date_created']) . ($pricelist['is_active'] ? (' |  Активен с: ' . $pricelist['date_start']) : ''))) ?><span style="color: grey"> (#<?= $pricelist['id'] ?>)</span>
            </td>
        </tr>
        <?php foreach ($pricelist['location'] as $location): ?>
            <tr>
                <td colspan="8" style="border-bottom: 2px solid black;text-align: center;">
                    <?= $location['text'] ?><span style="color: grey"> (#<?= $location['id'] ?>)</span>
                </td>
            </tr>
            <?php foreach ($location['filterA'] as $filterA): ?>
                <?php if ($filterA['prefix_count'] > 0): ?>
                    <?php $filterAStart = true; ?>
                    <tr>
                        <td rowspan="<?= $filterA['prefix_count'] ?>" style="text-align: center; vertical-align: middle; border-right: 1px solid lightgrey">
                            <?= $filterA['text'] ?><span style="color: grey"> (#<?= $filterA['id'] ?>)</span>
                        </td>
                        <?php foreach ($filterA['filterB'] as $filterB): ?>
                            <?php if ($filterB['prefix_count'] > 0): ?>
                                <?php $filterBStart = true; ?>
                                <?php if (!$filterAStart): ?>
                    <tr>
                        <?php endif; $filterAStart = false; ?>
                        <td rowspan="<?= $filterB['prefix_count'] ?>" style="text-align: center; vertical-align: middle; border-right: 1px solid lightgrey">
                            <?= $filterB['text'] ?>
                            <?php if ($filterB['use_for_minimum']): ?>
                                <span style="color: red;" popover="Используется для минималок" data-popover-trigger="mouseenter" popover-placement="bottom"> (MIN)</span>
                            <?php endif; ?>
                            <span style="color: grey"> (#<?= $filterB['id'] ?>)</span>
                        </td>
                        <td rowspan="<?= $filterB['prefix_count'] ?>" style="text-align: center; vertical-align: middle; border-right: 1px solid lightgrey">
                            <?php if ($filterB['rating'] !== ''): ?>
                                <span>Рейтинг:&nbsp;<?= $filterB['rating'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td rowspan="<?= $filterB['prefix_count'] ?>" style="text-align: center; vertical-align: middle; border-right: 1px solid lightgrey">
                            <span>И:&nbsp;<?= $filterB['interconnect_price'] ?></span>
                        </td>
                        <?php foreach ($filterB['prefixes'] as $prefixPriceArray): ?>
                            <?php if (!$filterBStart): ?>
                    <tr>
                        <?php endif; $filterBStart = false; ?>
                        <td><?= $prefixPriceArray[0]['prefix_b'] ?></td>
                        <td style="min-width: 250px;">
                            <span>
                                <?php foreach ($prefixPriceArray as $prefixPriceKey => $prefixPriceItem): ?>
                                    <?php if ($prefixPriceKey !== 0): ?>
                                        |
                                    <?php endif; ?>
                                    <?php if ($prefixPriceKey === 0): ?>
                                        <?= $prefixPriceItem['b_number_price'] ?>
                                    <?php endif; ?>
                                    <?php if ($prefixPriceKey === 0 && $prefixPriceItem['date_from'] > $dateNow): ?>
                                        <span style="color: blue;">+ c <?= $prefixPriceItem['date_from'] ?></span>
                                    <?php endif; ?>
                                    <?php if ($prefixPriceKey !== 0 && $prefixPriceItem['b_number_price'] < $prefixPriceArray[$prefixPriceKey - 1]['b_number_price']): ?>
                                        <span style="color: green;"><?= $prefixPriceItem['b_number_price'] ?> понижение c <?= $prefixPriceItem['date_from'] ?></span>
                                    <?php endif; ?>
                                    <?php if ($prefixPriceKey !== 0 && $prefixPriceItem['b_number_price'] > $prefixPriceArray[$prefixPriceKey - 1]['b_number_price']): ?>
                                        <span style="color: red;"><?= $prefixPriceItem['b_number_price'] ?> повышение c <?= $prefixPriceItem['date_from'] ?></span>
                                    <?php endif; ?>
                                    <?php if ($prefixPriceKey !== 0 && $prefixPriceItem['b_number_price'] == $prefixPriceArray[$prefixPriceKey - 1]['b_number_price']): ?>
                                        <span style="color: gray;"><?= $prefixPriceItem['b_number_price'] ?> продление c <?= $prefixPriceItem['date_from'] ?></span>
                                    <?php endif; ?>
                                    <!-- Исправлено условие для отображения "до" -->
                                    <?php if ($prefixPriceKey === count($prefixPriceArray) - 1 && $prefixPriceItem['date_to'] != '3000-01-01'): ?>
                                        <span style="color: black; font-weight: bold;">до <?= $prefixPriceItem['date_to'] ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <?php endforeach; ?>
    </tbody>
</table>
