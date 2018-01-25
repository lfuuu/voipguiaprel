var TestGenerateLogCtrl = function($scope, Scripts, $modalInstance, $window) {

    Scripts.viewTestsLog().then(function(data){
        $scope.item = data;
    });

    $scope.back = function()
    {
        $modalInstance.dismiss();
    }
};