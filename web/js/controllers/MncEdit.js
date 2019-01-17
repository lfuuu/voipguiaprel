var MncEditCtrl = function ($scope, Mnc, params, $modalInstance, $window) {

    if (params.mnc && params.mcc) {
        Mnc.get({mnc: params.mnc, mcc: params.mcc}).then(function (data) {
            $scope.item = data;
            $scope.edit = true;
        });
    } else {
        $scope.item = {};
        $scope.edit = false;
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