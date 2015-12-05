var TrunkEditCtrl = function($scope, Trunk, params, $modalInstance, $window) {

    if (params.id) {
        Trunk.get({id: params.id}).then(function(data){
            $scope.item = data;
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id,
            default_priority: 0,
            source_rule_default_allowed: false,
            destination_rule_default_allowed: false,
            priorities: [],
            rules: [],
            trunkRules: [],
            numberPreprocessing: []
        };
    }

    $scope.addPriority = function() {
        $scope.item.priorities.push({prefixlist_id: '', priority: 0});
    };

    $scope.removePriority = function(index) {
        $scope.item.priorities.splice(index, 1);
    };

    $scope.addRule = function(outgoing) {
        $scope.item.rules.push({prefixlist_id: '', outgoing: outgoing});
    };

    $scope.removeRule = function(index) {
        $scope.item.rules.splice(index, 1);
    };

    $scope.addTrunkRule = function() {
        $scope.item.trunkRules.push({trunk_group_id: ''});
    };

    $scope.removeTrunkRule = function(index) {
        $scope.item.trunkRules.splice(index, 1);
    };

    $scope.addNumberPreprocessing = function(src) {
        $scope.item.numberPreprocessing.push({src: src, noa: '', length: '', prefix: ''});
    };

    $scope.removeNumberPreprocessing = function(index) {
        $scope.item.numberPreprocessing.splice(index, 1);
    };

    $scope.save = function()
    {
        Trunk.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    }
};