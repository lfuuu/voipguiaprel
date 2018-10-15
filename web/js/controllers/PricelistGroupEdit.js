var PricelistGroupEditCtrl = function($scope, List, PricelistGroup, params, $modalInstance, $window) {

    if (params.id) {
        PricelistGroup.get({id: params.id}).then(function(data){
            $scope.item = data;
        });
    } else {
        $scope.item = {};
    }

    $scope.save = function()
    {
        PricelistGroup.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };
};