var SmsTestAuthEditCtrl = function($rootScope, $scope, Redirect, SmsTestAuth, List, params, $modalInstance, SmsList) {
    if (params.id) {
        SmsTestAuth.get({id: params.id}).then(function (data) {
            $scope.item = data;

            if (params.clone) {
                delete $scope.item.id;
            }
        });
    } else {
        $scope.item = {
            name: '',
            server_id: $scope.server.id
        };
    }

    SmsList.testGroup({}).then(function (data) {
        $scope.testGroupList = data;
    });

    List.serverOcs().then(function (data) {
        $scope.serverList = data;
    });

    $scope.save = function () {
        SmsTestAuth.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
