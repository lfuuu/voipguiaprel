var UserEditCtrl = function($rootScope, $scope, Redirect, User, SettingsList, params, $modalInstance) {
    if (params.id) {
        User.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name: '',
            assignment: []
        };
    }

    SettingsList.role().then(function (data) {
        $scope.roleList = data;

        $scope.roleFlatList = [];

        for (var i in data) {
            $scope.roleFlatList.push(data[i]['id']);
        }
    });

    $scope.save = function () {
        var itemToSave = angular.copy($scope.item);
    
        if ($scope.showChangePassword && itemToSave.newPassword && itemToSave.newPassword !== '') {
            itemToSave.password = itemToSave.newPassword;
        }
    
        if (!itemToSave.password || !$scope.showChangePassword) {
            delete itemToSave.password;
        }
    
        User.save(itemToSave).then(function (response) {
            $modalInstance.close();
        }, function (error) {
            alert('Error saving changes: ' + error.message);
        });
    };
    
    $scope.showChangePassword = false;

    $scope.toggleChangePassword = function() {
        $scope.showChangePassword = !$scope.showChangePassword;
    };

    $scope.onSelectChange = function () {
        if ($('#modalUserRoleSelect option[value="selectAll"]').is(':selected')) {
            $scope.item.assignment = $scope.roleFlatList;
        } else if ($('#modalUserRoleSelect option[value="selectNone"]').is(':selected')) {
            $scope.item.assignment = [];
        }
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };

};
