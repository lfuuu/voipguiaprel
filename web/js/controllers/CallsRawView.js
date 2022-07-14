var CallsRawViewCtrl = function ($scope, params, $modalInstance, $window) {

    if (params.item) {
        $scope.item = params.item;
        $scope.link = params.link;
    } else {
        $scope.item = [];
    }
    
    $scope.back = function () {
        $modalInstance.dismiss();
    }
};