var TestAuthEditCtrl = function($scope, $rootScope, TestAuth, TestCall, Redirect, List, params, $modalInstance, $window) {

    $scope.server_id = null;

    if (params.id) {
        if (params.cloneFromCall) {
            TestCall.get({id: params.id}).then(function (data) {

                var item = {
                    name: data.name,
                    server_id: data.server_id,
                    testgroup_id: data.testgroup_id,
                    trunk_name: data.src_trunk_name,
                    src_number: data.src_number,
                    dst_number: data.dst_number,
                    src_noa: data.src_noa,
                    dst_noa: data.dst_noa,
                    redirect_number: '',
                    with_debug_info: data.with_debug_info
                };

                $scope.item = item;
            });
        } else {
            TestAuth.get({id: params.id}).then(function (data) {
                $scope.item = data;

                if (params.clone) {
                    delete $scope.item.id;
                }
            });
        }
    } else {
        if (params.testGroupId) {
            $scope.item = {
                server_id: $scope.server.id,
                src_noa: 3,
                dst_noa: 3,
                redirect_number: '',
                testgroup_id: params.testGroupId,
                with_debug_info: false
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
                testgroup_id: params.default_params.testgroup_id,
                with_debug_info: false
            };

            $scope.server_id = params.default_params.server_id;
        } else {
            $scope.item = {
                server_id: $scope.server.id,
                src_noa: 3,
                dst_noa: 3,
                redirect_number: '',
                with_debug_info: false
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

    $scope.cloneToCall = function(id) {
        if (!userPermissions['test_call_create']) {
            return;
        }

        Redirect.testCallCloneFromAuth(id);
    };

    List.testGroup().then(function (data) {
        $scope.testGroupList = data;
    });

    $scope.save = function () {
        TestAuth.save($scope.item).then(function (response) {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};