var CamelTestGroupEditCtrl = function($rootScope, $scope, Redirect, CamelTestGroup, params, $modalInstance) {
    if (params.id) {
        CamelTestGroup.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name: ''
        };
    }

    $scope.save = function () {
        CamelTestGroup.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
