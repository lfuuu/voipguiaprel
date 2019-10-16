var CamelTrunkEditCtrl = function($rootScope, $scope, Redirect, CamelTrunk, CamelList, params, $modalInstance) {
    if (params.id) {
        CamelTrunk.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name: ''
        };
    }

    CamelList.gt().then(function (data) {
        $scope.gtList = data;
    });

    $scope.save = function () {
        CamelTrunk.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
