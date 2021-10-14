var AlphaNumberGroupEditCtrl = function($scope, List, AlphaNumberGroup, params, $modalInstance, $window, Redirect) {

    if (params.id) {
        AlphaNumberGroup.get({id: params.id}).then(function(data){
            $scope.item = data;
        });
    } else {
        $scope.item = {
            group_name: '',
        };
    }

    $scope.save = function()
    {
        AlphaNumberGroup.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    AlphaNumberGroup.listAlphaNumbers({id: params.id}).then(function (data) {
        $scope.alphaNumList = data;
    });

    $scope.listAlphaNumbers = function (item)
    {
        Redirect.alphaNumberGroupListAll(item.id).then(function () {
            $scope.init();
        });
    }

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };
};