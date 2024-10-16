var TrunkGroupEditCtrl = function($scope, List, TrunkGroup, params, $modalInstance, Redirect, $window) {
    $scope.pageIdSuffix = 'new';
    $scope.subTab = 'manual';
    $scope.selectedTab = 'trunk-group'
    $scope.usType = null; 
    $scope.filteredTrunks = [];
    $scope.isNewGroup = !params.id;

    if (params.id) {
        TrunkGroup.get({id: params.id}).then(function(data) {
            $scope.pageIdSuffix = params.id;
            $scope.item = data;
            $scope.initialServerId = $scope.item.server_id;

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

            TrunkGroup.findIntoRulesRoutingNums({id: params.id}).then(function(data) {
                $scope.item.findIntoRulesRoutingNums = data;
            });

            TrunkGroup.findIntoRulesAntifraud({id: params.id}).then(function(data) {
                $scope.item.findIntoRulesAntifraud = data;
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
            
            TrunkGroup.findRouteReplaceWithGroup({id: params.id}).then(function(data) {
                $scope.item.findRouteReplace = data;
            });
        });
    } else {

        $scope.item = {
            server_id: $scope.server.id,
            trunks: [],
            trunk_groups: []
        };
        
        $scope.initialServerId = $scope.item.server_id;
    }

    List.trunkByServer($scope.server.id).then(function(data) {
        $scope.trunkList = data;
    });

    List.trunkGroup().then(function(data) {
        $scope.trunkGroupList = data;
    });

    $scope.filterTrunksByUsType = function(usType) {
        if (!usType) {
            $scope.filteredTrunks = [];
            return;
        }

        var selectedUsType = parseInt(usType, 10);

        $scope.item.trunks = [];

        $scope.filteredTrunks = $scope.trunkList.filter(function(trunk) {
            return trunk.sorm_p268_us_type === selectedUsType;
        });

        if ($scope.filteredTrunks && $scope.filteredTrunks.length > 0) {
            $scope.filteredTrunks.forEach(function(trunk) {
                if ($scope.item.trunks.indexOf(trunk.id.toString()) === -1) {
                    $scope.item.trunks.push(trunk.id.toString());
                }
            });
        }

    };

    $scope.save = function() {
        if ($scope.initialServerId !== $scope.server.id) {
            alert("Изменения нельзя сохранить, так как вы пытаетесь изменить группу транков, которая находится на другом регионе.");
            return;
        }

        delete $scope.item.findIntoRules;
        delete $scope.item.findIntoPriorities;
        delete $scope.item.findIntoRulesRoutingNums;
        delete $scope.item.findIntoRulesAntifraud;
        delete $scope.item.findRouteTables;
        delete $scope.item.findOutcomes;
        delete $scope.item.findGroups;
        delete $scope.item.findRouteReplace;

        TrunkGroup.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function() {
        $modalInstance.dismiss();
    };

    $scope.hasPopover = function() {
        return $scope.item.used_in_marketplace ? 'mouseenter' : 'none';
    };
};
