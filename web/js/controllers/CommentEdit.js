var CommentEditCtrl = function ($scope, Comment, params, $modalInstance, $window) {
    $scope.itemTypes = {
        trunk: 'Транк',
        trunk_priority: 'Приоритет транка',
        trunk_numbers_rules: 'Правила оригинации/терминации',
        trunk_rules: 'Правила транка',
        trunk_preprocessing: 'Препроцессинг',
        trunk_sorm: 'СОРМ транк',
        trunk_load_limit: 'Ограничение загрузки',
        trunk_group: 'Группа транков',
        imsi_partner: 'IMSI партнеры',
        route_table: 'Таблица маршрутизации',
        number: 'А/В/С номера',
        outcome: 'Outcome',
        route_case: 'Route Case',
        prefixlist: 'Список префиксов',
        oca_bw: 'Список OCA BW',
        airp: 'AIRP',
        cpc: 'CPC',
        release_reason: 'Release Reason',
        header: 'Header',
        header_rule: 'Header Rule',
        attribute: 'Атрибут',
        attribute_group: 'Группа атрибутов',
        test_auth: 'Тест маршрутизации',
        test_call: 'Тест звонков',
        test_group: 'Группа тестов',
        trunk_group_item: 'Связь группы транков',
        route_table_route: 'Маршрут маршрутизации',
        route_table_rule: 'Правило маршрутизации',
        route_case_trunk: 'Route case транк'
    };

    $scope.item = {
        object_id: params.object_id,
        object_type: params.object_type,
        object_type_name: $scope.itemTypes[params.object_type],
        object_comment: params.object_comment
    };

    $scope.save = function () {
        if ($scope.item.object_id) {
            Comment.save({
                object_id: $scope.item.object_id,
                object_type: $scope.item.object_type,
                object_comment: $scope.item.object_comment
            }).then(function () {
                $modalInstance.close($scope.item.object_comment);
            });
        } else {
            $modalInstance.close($scope.item.object_comment);
        }
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};