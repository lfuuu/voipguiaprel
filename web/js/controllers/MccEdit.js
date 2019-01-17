var MccEditCtrl = function ($scope, Mcc, params, $modalInstance, $window) {

    if (params.mcc) {
        Mcc.get({mcc: params.mcc}).then(function (data) {
            $scope.item = data;
            $scope.edit = true;
        });
    } else {
        $scope.item = {};
        $scope.edit = false;
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