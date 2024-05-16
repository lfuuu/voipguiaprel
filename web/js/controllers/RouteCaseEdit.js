var RouteCaseEditCtrl = function ($scope, Redirect, RouteCase, params, $modalInstance, $window) {

    if (params.id) {
        RouteCase.get({id: params.id}).then(function (data) {
            $scope.item = data;
            if ($scope.item.trunks === undefined) {
                $scope.item.trunks = [];
            }

            $scope.initialServerId = $scope.item.server_id;

        });

        RouteCase.findUsagesInOutcomes({id: params.id}).then(function (data) {
            $scope.usagesInOutcomes = data;
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id,
            trunks: []
        };

        $scope.initialServerId = $scope.item.server_id;

    }

    $scope.addTrunk = function () {
        $scope.item.trunks.push({trunk_id: null, priority: 1, weight: 100});
    };

    $scope.removeTrunk = function (index) {
        $scope.item.trunks.splice(index, 1);
    };

    $scope.save = function () {

        if ($scope.initialServerId !== $scope.server.id) {
            alert("Изменения нельзя сохранить, так как вы пытаетесь изменить Route Case, который находится на другом регионе.");
            return;
        }

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
    };
};
