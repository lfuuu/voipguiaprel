var TrunkLogicCtrl = function($rootScope, $scope, Redirect, Trunk, List, params, $modalInstance, STAT_HOST, $window)  {
    if (params.item.id) {
        $scope.item = {
            id: params.id,
            item:params.item
        };
        
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