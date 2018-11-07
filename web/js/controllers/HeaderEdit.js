var HeaderEditCtrl = function ($scope, Header, params, $modalInstance, $window) {

    if (params.id) {
        Header.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {};
    }

    $scope.save = function () {
        Header.save($scope.item).then(function (response) {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    }
};