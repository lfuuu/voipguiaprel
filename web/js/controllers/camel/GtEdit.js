var CamelGtEditCtrl = function($rootScope, $scope, Redirect, CamelGt, params, $modalInstance) {
    if (params.id) {
        CamelGt.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name: ''
        };
    }

    $scope.save = function () {
        CamelGt.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
