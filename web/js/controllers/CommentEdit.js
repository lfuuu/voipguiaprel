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

    $scope.objectId = params.object_id;
    $scope.objectType = params.object_type;
    $scope.objectTypeName = $scope.itemTypes[params.object_type];
    $scope.objectComment = params.object_comment;

    $scope.save = function () {
        Comment.save({object_id: $scope.objectId, object_type: $scope.objectType, object_comment: $scope.objectComment}).then(function () {
            $modalInstance.close($scope.objectComment);
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};