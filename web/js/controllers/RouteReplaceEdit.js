var RouteReplaceEditCtrl = function ($scope, RouteReplace, List, params, $modalInstance, $window) {
    $scope.items = [];

    $scope.origAttrList = List.origAttribute();
    $scope.termAttrList = List.termAttribute();

    RouteReplace.read({server_id: $scope.server.id}).then(function (data) {
        $scope.items = data;
    });

    $scope.sortableOptions = {
        update: function (e, ui) {
            if (ui.item.sortable.index < ui.item.sortable.dropindex) {
                var dropMin = ui.item.sortable.index;
                var dropMax = ui.item.sortable.dropindex;
            } else {
                var dropMin = ui.item.sortable.dropindex;
                var dropMax = ui.item.sortable.index;
            }
        },
        axis: 'y'
    };

    $scope.add = function () {
        $scope.items.push({action_type: 'accept', is_orig_group: false, is_term_group: false});
    };

    $scope.remove = function (index) {
        $scope.items.splice(index, 1);
    };

    $scope.save = function () {
        var toSave;

        RouteReplace.saveMultiple({server_id: $scope.server.id, items: $scope.items}).then(function (response) {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};

