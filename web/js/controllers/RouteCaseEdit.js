var RouteCaseEditCtrl = function ($scope, Redirect, RouteCase, params, $modalInstance, $window) {

    if (params.id) {
        RouteCase.get({id: params.id}).then(function (data) {
            $scope.item = data;
            if ($scope.item.trunks === undefined) {
                $scope.item.trunks = [];
            }
        });

        RouteCase.findUsagesInOutcomes({id: params.id}).then(function (data) {
            $scope.usagesInOutcomes = data;
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id,
            trunks: []
        };
    }

    $scope.addTrunk = function () {
        $scope.item.trunks.push({trunk_id: null, priority: 1, weight: 100});
    };

    $scope.removeTrunk = function (index) {
        $scope.item.trunks.splice(index, 1);
    };


    $scope.save = function () {
        RouteCase.save($scope.item).then(function (response) {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };

    $scope.clickOutcomeItem = function (item) {
        if (window.getSelection().type == 'Range') return;

        Redirect.outcomeEdit(item.id).then(function () {
            $scope.init();
        });
    }
};