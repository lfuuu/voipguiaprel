var TrunkLogicCtrl = function($rootScope, $scope, Redirect, Trunk, List, params, $modalInstance, STAT_HOST, $window)  {
    if (params.item.id) {
        $scope.item = {
            id: params.id,
            item:params.item
        };
        $scope.link = ($window.location.hostname).includes('.tech') ? 'https://stat.kompaas.tech/' : 'https://stat.mcn.ru/';
        Trunk.serviceTrunks({id: params.item.id}).then(function(data){
            $scope.list = data;
        });
    } else {
        $scope.list = [];
    }

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };
};