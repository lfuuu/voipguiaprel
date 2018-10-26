var TestPricelistGroupEditCtrl = function($scope, TestPricelistGroup, params, $modalInstance, $window) {

    if (params.id) {
        TestPricelistGroup.get({id: params.id}).then(function(data){
            $scope.item = data;
        });
    } else {
        $scope.item = {}
    }

    $scope.save = function()
    {
        TestPricelistGroup.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    }
};