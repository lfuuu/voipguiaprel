<?php

/** @var \app\models\Trunk $trunk */
?>

<div class="well col-sm-12" style="padding-top: 60px;">
    <div class="col-sm-6">
        <table class="table table-striped table-hover table-condensed">
            <thead>
                <tr>
                    <th colspan="2">Транк</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Регион</td>
                    <td><?= ($trunk->server_id ? $trunk->server->name : '---') ?></td>
                </tr>
                <tr>
                    <td>Ид</td>
                    <td><?= $trunk->id ?></td>
                </tr>
                <tr>
                    <td>Название</td>
                    <td><?= $trunk->name ?></td>
                </tr>
                <tr>
                    <td>Имя транка</td>
                    <td><?= $trunk->trunk_name ?></td>
                </tr>
                <tr>
                    <td>Имя транка (Alias)</td>
                    <td><?= ($trunk->trunk_name_alias ?: '---') ?></td>
                </tr>
                <tr>
                    <td>Емкость транка</td>
                    <td><?= ($trunk->capacity ?: '---') ?></td>
                </tr>
                <tr>
                    <td>Порог загрузки %</td>
                    <td><?= ($trunk->load_warning ?: '---') ?></td>
                </tr>
                <tr>
                    <td>Путь в регион</td>
                    <td><?= ($trunk->road_to_region ?: '---') ?></td>
                </tr>
                <tr>
                    <td>Автоматическая маршрутизация включена</td>
                    <td><?= ($trunk->auto_routing ? 'Да' : 'Нет') ?></td>
                </tr>
                <tr>
                    <td>Наш транк</td>
                    <td><?= ($trunk->our_trunk ? 'Да' : 'Нет') ?></td>
                </tr>
                <tr>
                    <td>Авторизация по номеру</td>
                    <td><?= ($trunk->auth_by_number ? 'Да' : 'Нет') ?></td>
                </tr>
                <tr>
                    <td>Тех. номер 7800</td>
                    <td><?= ($trunk->orig_redirect_number_7800 ? 'Да' : 'Нет') ?></td>
                </tr>
                <tr>
                    <td>Redirecting Оригинация</td>
                    <td><?= ($trunk->orig_redirect_number ? 'Да' : 'Нет') ?></td>
                </tr>
                <tr>
                    <td>Redirecting Терминация</td>
                    <td><?= ($trunk->term_redirect_number ? 'Да' : 'Нет') ?></td>
                </tr>
                <tr>
                    <td>Оригинация</td>
                    <td><?= ($trunk->trunkOrigTerm && $trunk->trunkOrigTerm->orig_enabled ? 'Да' : 'Нет') ?></td>
                </tr>
                <tr>
                    <td>Терминация</td>
                    <td><?= ($trunk->trunkOrigTerm && $trunk->trunkOrigTerm->term_enabled ? 'Да' : 'Нет') ?></td>
                </tr>
                <tr>
                    <td>Минималки на Терм-плече</td>
                    <td><?= ($trunk->sw_minimalki ? 'Да' : 'Нет') ?></td>
                </tr>
                <tr>
                    <td>Доступен на хабе</td>
                    <td><?= ($trunk->sw_shared ? 'Да' : 'Нет') ?></td>
                </tr>
                <tr>
                    <td>Показыть в СТАТ</td>
                    <td><?= ($trunk->show_in_stat ? 'Да' : 'Нет') ?></td>
                </tr>
                <tr>
                    <td>Таблица маршрутизации</td>
                    <td>
                        <?= ($trunk->route_table_id ? $trunk->routeTable->name : '---') ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="col-sm-6">
        <table class="table table-striped table-hover table-condensed">
            <colgroup>
                <col width="10%" />
                <col width="*" />
                <col width="15%" />
                <col width="15%" />
            </colgroup>
            <thead>
                <tr>
                    <th colspan="4">Приоритеты (По умолчанию: <?= $trunk->default_priority ?>)</th>
                </tr>
                <tr>
                    <td>Приоритет</td>
                    <td>Группа транков</td>
                    <td>А-номер</td>
                    <td>В-номер</td>
                </tr>
            </thead>
            <tbody>
                <?php if (count($trunk->priorities)) :?>
                    <?php foreach ($trunk->priorities as $line) :?>
                        <tr>
                            <td>
                                <?= $line->priority ?>
                            </td>
                            <td>
                                <?= ($line->trunk_group_id ? $line->trunkGroup->name : 'Любая группа') ?>
                            </td>
                            <td>
                                <?= ($line->number_id_filter_a ? $line->numberA->name : 'Любой номер') ?>
                            </td>
                            <td>
                                <?= ($line->number_id_filter_b ? $line->numberB->name : 'Любой номер') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4">Не задано</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <table class="table table-striped table-hover table-condensed">
            <colgroup>
                <col width="20%" />
                <col width="*" />
            </colgroup>
            <thead>
                <tr>
                    <th colspan="2">Правила по А-номеру</th>
                </tr>
                <tr>
                    <td>По умолчанию</td>
                    <td>Список префиксов</td>
                </tr>
            </thead>
            <tbody>
                <?php if (count($trunk->rulesSource)) :?>
                    <?php foreach ($trunk->rulesSource as $rule) : ?>
                        <tr>
                            <td>
                                <?= ($trunk->source_rule_default_allowed ? 'Запрещено' : 'Разрешено') ?>
                            </td>
                            <td>
                                <?= ($rule->prefixlist_id ? $rule->prefixlist->name : '') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="2">Не задано</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <table class="table table-striped table-hover table-condensed">
            <colgroup>
                <col width="20%" />
                <col width="*" />
            </colgroup>
            <thead>
                <tr>
                    <th colspan="2">Правила по B-номеру</th>
                </tr>
                <tr>
                    <td>По умолчанию</td>
                    <td>Список префиксов</td>
                </tr>
            </thead>
            <tbody>
                <?php if (count($trunk->rulesDestination)) : ?>
                    <?php foreach ($trunk->rulesDestination as $rule) : ?>
                        <tr>
                            <td>
                                <?= ($trunk->source_rule_default_allowed ? 'Запрещено' : 'Разрешено') ?>
                            </td>
                            <td>
                                <?= ($rule->prefixlist_id ? $rule->prefixlist->name : '') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="2">Не задано</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <table class="table table-striped table-hover table-condensed">
            <colgroup>
                <col width="20%" />
                <col width="*" />
                <col width="15%" />
                <col width="15%" />
            </colgroup>
            <thead>
                <tr>
                    <th colspan="4">Правила транков</th>
                </tr>
                <tr>
                    <td>По умолчанию</td>
                    <td>Группа транков</td>
                    <td>А-номер</td>
                    <td>В-номер</td>
                </tr>
            </thead>
            <tbody>
                <?php if (count($trunk->trunkRules)) : ?>
                    <?php foreach ($trunk->trunkRules as $rule) : ?>
                        <tr>
                            <td>
                                <?= ($trunk->source_rule_default_allowed ? 'Запрещено' : 'Разрешено') ?>
                            </td>
                            <td>
                                <?= ($rule->trunk_group_id ? $rule->trunkGroup->name : '') ?>
                            </td>
                            <td>
                                <?= ($rule->number_id_filter_a ? $rule->numberA->name : 'Любой номер') ?>
                            </td>
                            <td>
                                <?= ($rule->number_id_filter_b ? $rule->numberB->name : 'Любой номер') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4">Не задано</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <table class="table table-striped table-hover table-condensed">
            <colgroup>
                <col width="10%" />
                <col width="30%" />
                <col width="30%" />
                <col width="30%" />
            </colgroup>
            <thead>
                <tr>
                    <th colspan="4">Препроцессинг номеров</th>
                </tr>
                <tr>
                    <td></td>
                    <td>NOA</td>
                    <td>Длина номера</td>
                    <td>Префикс</td>
                </tr>
            </thead>
            <tbody>
                <?php if (count($trunk->numberPreprocessing)) :?>
                    <?php foreach ($trunk->numberPreprocessing as $line) : ?>
                        <tr>
                            <td>
                                <?= ($line->src ? 'A-номер' : 'B-номер') ?>
                            </td>
                            <td>
                                <?= $line->noa ?>
                            </td>
                            <td>
                                <?= $line->length ?>
                            </td>
                            <td>
                                <?= $line->prefix ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4">Не задано</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>