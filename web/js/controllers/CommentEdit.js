var CommentEditCtrl = function ($scope, Comment, params, $modalInstance, $window) {
    $scope.itemTypes = {
        trunk: 'Транк',
        trunk_priority: 'Приоритет транка',
        trunk_numbers_rules: 'Правила оригинации/терминации',
        trunk_rules: 'Правила транка',
        trunk_preprocessing: 'Препроцессинг',
        trunk_sorm: 'СОРМ транк',
        trunk_load_limit: 'Ограничение загрузки'
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