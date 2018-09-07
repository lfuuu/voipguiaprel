var ImsiPartnerEditCtrl = function ($scope, List, ImsiPartner, params, $modalInstance, $window) {

    $scope.location = List.location();

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