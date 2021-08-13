var AlphaNumberEditCtrl = function($scope, List, AlphaNumber, params, $modalInstance, $window) {

    if (params.id) {
        AlphaNumber.get({id: params.id}).then(function(data){
            $scope.item = data;
        });
    } else {
        $scope.item = {
            alphanum: '',
        };
    }

    $scope.save = function()
    {
        AlphaNumber.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };
};