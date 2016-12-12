var AttributeEditCtrl = function ($scope, Attribute, params, $modalInstance, $window) {

    if (params.id) {
        Attribute.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id
        };
    }


    $scope.save = function () {
        Attribute.save($scope.item).then(function (response) {
            $modalInstance.close();
        });
    }

    $scope.back = function () {
        $modalInstance.dismiss();
    }
};