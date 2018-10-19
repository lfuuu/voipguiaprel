var MccEditCtrl = function ($scope, Mcc, params, $modalInstance, $window) {

    if (params.mcc) {
        Mcc.get({mcc: params.mcc}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {};
    }

    $scope.save = function () {
        Mcc.save($scope.item).then(function (response) {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    }
};