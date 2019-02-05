var HeaderRuleEditCtrl = function ($scope, HeaderRule, List, params, $modalInstance, $window) {
    $scope.headerRuleItemModeList = List.headerRuleItemMode();

    if (params.id) {
        HeaderRule.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id,
            items: [],
        };
    }

    $scope.save = function () {
        HeaderRule.save($scope.item).then(function (response) {
            $modalInstance.close();
        });
    };

    $scope.sortableOptions = {
        update: function (e, ui) {
            var sortBlocked = false;

            if (ui.item.sortable.index < ui.item.sortable.dropindex) {
                var dropMin = ui.item.sortable.index;
                var dropMax = ui.item.sortable.dropindex;
            } else {
                var dropMin = ui.item.sortable.dropindex;
                var dropMax = ui.item.sortable.index;
            }

            for (var itemKey in $scope.item.items) {
                if (itemKey >= dropMin && itemKey <= dropMax && $scope.item.items[itemKey]['is_locked']) {
                    sortBlocked = true;
                }
            }

            if (sortBlocked) {
                ui.item.sortable.cancel();
            }
        },
        axis: 'y'
    };

    $scope.addItem = function () {
        $scope.item.items.push({});
    };

    $scope.removeItem = function (index) {
        $scope.item.items.splice(index, 1);
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    }
};