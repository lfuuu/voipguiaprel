var TrunkGroupEditCtrl = function($scope, List, TrunkGroup, params, $modalInstance, Redirect, $window) {
    $scope.pageIdSuffix = 'new';

    if (params.id) {
        TrunkGroup.get({id: params.id}).then(function(data){
            $scope.pageIdSuffix = params.id;
            $scope.item = data;
            var trunks = [];
            var trunk_groups = [];

            if ($scope.item.trunks === undefined) {
                $scope.item.trunks = [];
            } else {
                for (var i in data.trunks) {
                    trunks.push(data.trunks[i].trunk_id);
                }
                $scope.item.trunks = trunks;
            }

            if ($scope.item.trunk_groups === undefined) {
                $scope.item.trunk_groups = [];
            } else {
                for (var i in data.trunk_groups) {
                    trunk_groups.push(data.trunk_groups[i].child_trunk_group_id);
                }
                $scope.item.trunk_groups = trunk_groups;
            }

            TrunkGroup.findIntoRules({id: params.id}).then(function(data) {
                $scope.item.findIntoRules = data;
            });

            TrunkGroup.findIntoPriorities({id: params.id}).then(function(data) {
                $scope.item.findIntoPriorities = data;
            });

            TrunkGroup.findRouteTablesWithGroup({id: params.id}).then(function(data) {
                $scope.item.findRouteTables = data;
            });

            TrunkGroup.findOutcomesWithGroup({id: params.id}).then(function(data) {
                $scope.item.findOutcomes = data;
            });

            TrunkGroup.findGroupsWithGroup({id: params.id}).then(function(data) {
                $scope.item.findGroups = data;
            });
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id,
            trunks: [],
            trunk_groups: []
        };
    }

    List.trunkGroup().then(function (data) {
        $scope.trunkGroupList = data;
    });

    List.trunkByServer($scope.server.id).then(function (data) {
        $scope.trunkList = data;
    });

    $scope.clickTrunkItem = function(trunkId) {
        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect.trunkEdit(trunkId).then(function () {
            $scope.init();
        });
    };

    $scope.clickRouteTableItem = function(routeTableId) {
        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect.routeTableEdit(routeTableId).then(function () {
            $scope.init();
        });
    };

    $scope.clickOutcomeItem = function(outcomeId) {
        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect.outcomeEdit(outcomeId).then(function () {
            $scope.init();
        });
    };

    $scope.clickGroupItem = function(trunkGroupId) {
        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect.trunkGroupEdit(trunkGroupId).then(function () {
            $scope.init();
        });
    };

    $scope.save = function() {
        delete $scope.item.findIntoRules;
        delete $scope.item.findIntoPriorities;

        TrunkGroup.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function() {
        $modalInstance.dismiss();
    };

    $scope.hasPopover = function () {
        return $scope.item.used_in_marketplace ? 'mouseenter' : 'none';
    };
};