var CallsRawViewCtrl = function ($scope, Cdr, params, $modalInstance, $window) {

    if (params.item) {
        $scope.item = params.item;
    } else {
        $scope.item = [];
    }

    $scope.back = function () {
        $modalInstance.dismiss();
    }
};