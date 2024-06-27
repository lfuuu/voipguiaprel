var TestDialEditCtrl = function($scope, $http, TestDial, List, params, $modalInstance, $window, Settings) {
    if (params.id) {
        TestDial.get({id: params.id}).then(function (data) {
            $scope.item = data;

            if (params.clone) {
                delete $scope.item.id;
            }
        });
    } else {
        $scope.item = {
            server_id: {
                id: $scope.server.id
            },
            src_noa: 3,
            dst_noa: 3,
            redirect_noa: 3,
            redirect_number: '',
            testgroup_id: params.testGroupId || undefined,
            orig: false,
            session_time: 60,
            with_debug_info: false,
            nas_ip_address: null
        };
    }

    List.trunk().then(function (data) {
        $scope.trunkList = data;
    });

    List.testGroup().then(function (data) {
        $scope.testGroupList = data;
    });

    Settings.getNasIpAddress($scope.server.id).then(function(response) {
        var nasIpAddresses = response.nas_ip_address;
        $scope.serverList = nasIpAddresses.split(',');
        if ($scope.item.nas_ip_address === null && $scope.serverList.length > 0) {
            $scope.item.nas_ip_address = $scope.serverList[0];
        } 
    }).catch(function(error) {
            console.error('Error loading NAS IP addresses:', error);
    });
    
    

    $scope.save = function() {
        var itemToSave = angular.copy($scope.item);
        itemToSave.server_id = itemToSave.server_id.id || itemToSave.server_id;
    
        TestDial.save(itemToSave).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function() {
        $modalInstance.dismiss();
    };

    $scope.hasPopover = function () {
        return $scope.item.is_autotest ? 'mouseenter' : 'none';
    };
};
