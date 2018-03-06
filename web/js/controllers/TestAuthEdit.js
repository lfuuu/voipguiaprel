var TestAuthEditCtrl = function($scope, $rootScope, TestAuth, List, params, $modalInstance, $window) {

    $scope.server_id = null;

    if (params.id) {
        TestAuth.get({id: params.id}).then(function(data){
            $scope.item = data;

            if (params.clone) {
                delete $scope.item.id;
            }
        });
    } else {
        if (params.testGroupId) {
            $scope.item = {
                server_id: $scope.server.id,
                src_noa: 3,
                dst_noa: 3,
                redirect_number: '',
                testgroup_id: params.testGroupId
            }
        } else if (params.default_params) {
            $scope.item = {
                src_noa: 3,
                dst_noa: 3,
                redirect_number: '',
                server_id: params.default_params.server_id,
                trunk_name: params.default_params.trunk_name,
                dst_number: params.default_params.dst_number,
                src_number: params.default_params.src_number,
                testgroup_id: params.default_params.testgroup_id
            };

            $scope.server_id = params.default_params.server_id;
        } else {
            $scope.item = {
                server_id: $scope.server.id,
                src_noa: 3,
                dst_noa: 3,
                redirect_number: ''
            }
        }
    }

    if ($scope.server_id) {
        List.trunkByServer($scope.server_id).then(function (data) {
            $scope.trunkList = data;
        });
    } else {
        List.trunk().then(function (data) {
            $scope.trunkList = data;
        });
    }

    List.testGroup().then(function (data) {
        $scope.testGroupList = data;
    });

    $scope.save = function()
    {
        TestAuth.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    }
};