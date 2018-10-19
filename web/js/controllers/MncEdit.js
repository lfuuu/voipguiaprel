var MncEditCtrl = function ($scope, Mnc, params, $modalInstance, $window) {

    if (params.mnc) {
        Mnc.get({mnc: params.mnc}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {};
    }

    $scope.save = function () {
        Mnc.save($scope.item).then(function (response) {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    }
};