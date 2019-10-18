var RoleEditCtrl = function($rootScope, $scope, Redirect, Role, SettingsList, params, $modalInstance) {
    if (params.name) {
        Role.get({name: params.name}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name: ''
        };
    }

    SettingsList.acl().then(function (data) {
        $scope.aclList = data;
        $scope.aclFlatList = [];

        for (var i in data) {
            $scope.aclFlatList.push(data[i]['id']);
        }
    });

    $scope.save = function () {
        Role.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.onSelectChange = function () {
        if ($('#modalRoleSelect option[value="selectAll"]').is(':selected')) {
            $scope.item.roleAcl = $scope.aclFlatList;
        } else if ($('#modalRoleSelect option[value="selectNone"]').is(':selected')) {
            $scope.item.roleAcl = [];
        }
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
