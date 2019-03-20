var TestCallEditCtrl = function($scope, TestCall, TestAuth, List, Redirect, params, $modalInstance, $window) {

    if (params.id) {
        if (params.cloneFromAuth) {
            TestAuth.get({id: params.id}).then(function (data) {

                var item = {
                    name: data.name,
                    server_id: data.server_id,
                    testgroup_id: data.testgroup_id,
                    src_trunk_name: data.trunk_name,
                    src_number: data.src_number,
                    dst_number: data.dst_number,
                    src_noa: data.src_noa,
                    dst_noa: data.dst_noa,
                    redirect_number: '',
                    orig: true,
                    connect_time: 'now()',
                    session_time: 60,
                    with_debug_info: data.with_debug_info
                };

                $scope.item = item;
            });
        } else {
            TestCall.get({id: params.id}).then(function (data) {
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
                orig: false,
                session_time: 60,
                with_debug_info: false
            }
        } else {
            $scope.item = {
                server_id: $scope.server.id,
                src_noa: 3,
                dst_noa: 3,
                redirect_number: '',
                orig: false,
                session_time: 60,
                with_debug_info: false
            }
        }
    }

    List.trunk().then(function (data) {
        $scope.trunkList = data;
    });

    List.testGroup().then(function (data) {
        $scope.testGroupList = data;
    });

    $scope.cloneToAuth = function(id) {
        if (!userPermissions['test_auth_create']) {
            return;
        }

        Redirect.testAuthCloneFromCall(id);
    };

    $scope.save = function()
    {
        TestCall.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };

    $scope.hasPopover = function () {
        return $scope.item.is_autotest ? 'mouseenter' : 'none';
    };
};