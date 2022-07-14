var PricelistInCommerceCtrl = function($scope, $rootScope, Redirect, Pricelist, params, $modalInstance, $window) {
    if (params.id) {
        $scope.item = {
            id: params.id
        };
        $scope.link = ($window.location.hostname).includes('.tech') ? 'https://stat.kompaas.tech/' : 'https://stat.mcn.ru/';
        Pricelist.getInCommerce({id: params.id}).then(function(data){
            $scope.list = data;
        });
    } else {
        $scope.list = [];
    }

    $scope.openTrunk = function(trunkId, serverId) {
        if (!userPermissions['trunk_edit']) {
            return;
        }
        
        $rootScope.server = {
            id: serverId
        };

        Redirect.trunkEditByServer(trunkId, serverId).then(function () {
            $scope.init();
        });
    };
    
    $scope.back = function()
    {
        $modalInstance.dismiss();
    };
};