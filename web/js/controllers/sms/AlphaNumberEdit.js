var AlphaNumberEditCtrl = function($scope, List, AlphaNumber, AlphaNumberGroup, params, $modalInstance, $window) {

    if (params.id) {
        AlphaNumber.get({id: params.id}).then(function(data){
            $scope.item = data;
            $scope.setAlphaNumberGroup();
        });
    } else {
        $scope.item = {
            alphanum: '',
            group_id: '',
        };
    }

    $scope.save = function()
    {
        AlphaNumber.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    AlphaNumberGroup.alphaNumGroupList().then(function (data) {
        $scope.alphaNumGroupList = data;
    });

    $scope.setAlphaNumberGroup = function() {
        if ($scope.item.group_id !== '') {
            $scope.item.group_id = $scope.item.group_id;
        }
    }

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };
};