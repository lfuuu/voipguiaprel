var TrunkGroupEditCtrl = function($scope, List, TrunkGroup, params, $modalInstance, Redirect, $window) {
    $scope.pageIdSuffix = 'new';
    $scope.item = {};
    $scope.item.input_type = 'manual';
    $scope.selectedTab = 'trunk-group';
    $scope.usType = null; 
    $scope.filteredTrunks = [];
    $scope.isNewGroup = !params.id;

    $scope.setInputType = function(type) {
        $scope.item.input_type = type;
    };

    if (params.id) {
        TrunkGroup.get({id: params.id}).then(function(data) {
            $scope.pageIdSuffix = params.id;
            $scope.item = data;
            $scope.initialServerId = $scope.item.server_id;
            $scope.item.input_type = data.input_type || 'manual';

            // Initialize separate trunks arrays based on input type
            $scope.item.manual_trunks = [];
            $scope.item.us_belonging_trunks = [];

            if ($scope.item.input_type == 'manual') {
                if (data.trunks) {
                    $scope.item.manual_trunks = data.trunks.map(function(trunk) {
                        return trunk.trunk_id.toString();
                    });
                }
            } else if ($scope.item.input_type == 'us_belonging') {
                if (data.trunks) {
                    $scope.item.us_belonging_trunks = data.trunks.map(function(trunk) {
                        return trunk.trunk_id.toString();
                    });
                }
            }

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
            
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id,
            manual_trunks: [],
            us_belonging_trunks: [],
            input_type: 'manual'
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

        $scope.item.us_belonging_trunks = [];

        $scope.filteredTrunks = $scope.trunkList.filter(function(trunk) {
            return trunk.sorm_p268_us_type === selectedUsType;
        });

        if ($scope.filteredTrunks && $scope.filteredTrunks.length > 0) {
            $scope.filteredTrunks.forEach(function(trunk) {
                if ($scope.item.us_belonging_trunks.indexOf(trunk.id.toString()) === -1) {
                    $scope.item.us_belonging_trunks.push(trunk.id.toString());
                }
            });
        }
    };

    $scope.save = function() {
        if ($scope.initialServerId !== $scope.server.id) {
            alert("Изменения нельзя сохранить, так как вы пытаетесь изменить группу транков, которая находится на другом регионе.");
            return;
        }

        // Prepare data for saving
        var dataToSave = angular.copy($scope.item);

        // Depending on the input type, assign the appropriate trunks
        if ($scope.item.input_type == 'manual') {
            dataToSave.trunks = $scope.item.manual_trunks;
            // Since IDs are unique within their type, ensure correct handling here
            dataToSave.trunks_type = 'manual';
        } else if ($scope.item.input_type == 'us_belonging') {
            dataToSave.trunks = $scope.item.us_belonging_trunks;
            dataToSave.trunks_type = 'us_belonging';
        }

        // Clean up properties not needed in the save
        delete dataToSave.manual_trunks;
        delete dataToSave.us_belonging_trunks;
        delete dataToSave.findIntoRules;
        delete dataToSave.findIntoPriorities;
        delete dataToSave.findIntoRulesRoutingNums;
        delete dataToSave.findIntoRulesAntifraud;
        delete dataToSave.findRouteTables;
        delete dataToSave.findOutcomes;
        delete dataToSave.findGroups;
        delete dataToSave.findRouteReplace;

        TrunkGroup.save(dataToSave).then(function(response) {
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