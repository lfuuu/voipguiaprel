var AttributeGroupEditCtrl = function ($scope, AttributeGroup, Attribute, params, $modalInstance, $window) {

    if (params.id) {
        AttributeGroup.get({id: params.id}).then(function (data) {
            $scope.item = data;
            var attributeslist_ids = [];
            for (var i in $scope.item.attributeslist_ids) {
                attributeslist_ids.push({id: $scope.item.attributeslist_ids[i]})
            }
            $scope.item.attributeslist_ids = attributeslist_ids;
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id,
            attributeslist_ids: []
        };
    }

    Attribute.list().then(function (data) {
        $scope.attributeslistList = data;
    });

    $scope.addAttribute = function () {
        $scope.item.attributeslist_ids.push({id: null});
    };

    $scope.removeAttribute = function (index) {
        $scope.item.attributeslist_ids.splice(index, 1);
    };

    $scope.save = function () {

        var data = angular.copy($scope.item);
        data.attributeslist_ids = [];
        for (var i in $scope.item.attributeslist_ids) {
            data.attributeslist_ids.push($scope.item.attributeslist_ids[i].id)
        }

        AttributeGroup.save($scope.item).then(function (response) {
            $modalInstance.close();
        });
    }

    $scope.back = function () {
        $modalInstance.dismiss();
    }
};