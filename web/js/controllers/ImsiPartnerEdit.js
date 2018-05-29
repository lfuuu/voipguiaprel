var ImsiPartnerEditCtrl = function ($scope, Redirect, ImsiPartner, params, $modalInstance, $window) {

    if (params.id) {
        ImsiPartner.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {};
    }

    $scope.save = function () {
        ImsiPartner.save($scope.item).then(function (response) {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };

};