var TrunkGroupEditCtrl = function($scope, TrunkGroup, params, $modalInstance, Redirect, $window) {

    if (params.id) {
        TrunkGroup.get({id: params.id}).then(function(data){
            $scope.item = data;
            if ($scope.item.trunks === undefined) {
                $scope.item.trunks = [];
            }

            TrunkGroup.extendsInfo({id: params.id}).then(function(data) {
                $scope.item.extendsInfo = data;
            });
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id,
            trunks: []
        };
    }

    $scope.clickTrunkItem = function(trunkId) {
        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect.trunkEdit(trunkId).then(function () {
            $scope.init();
        });
    };

    $scope.addTrunkGroup = function() {
        $scope.item.trunks.push({trunk_id: null});
    };

    $scope.removeTrunkGroup = function(index) {
        $scope.item.trunks.splice(index, 1);
    };

    $scope.save = function() {
        TrunkGroup.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function() {
        $modalInstance.dismiss();
    }

};