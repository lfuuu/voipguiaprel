var CamelTestAuthEditCtrl = function($rootScope, $scope, Redirect, CamelTestAuth, CamelList, List, params, $modalInstance) {
    if (params.id) {
        CamelTestAuth.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name: '',
            server_id: $scope.server.id
        };
    }

    CamelList.testGroup({}).then(function (data) {
        $scope.testGroupList = data;
    });

    List.serverOcs().then(function (data) {
        $scope.serverList = data;
    });

    $scope.save = function () {
        CamelTestAuth.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
