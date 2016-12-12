var AttributeGroupListCtrl = function ($scope, AttributeGroup, Redirect, $window) {

    $scope.init = function (tab) {
        if (tab) tab.title = 'AttributeGroup';

        AttributeGroup.read().then(function (data) {
            $scope.list = data;
        });
    };

    $scope.clickCreate = function () {
        Redirect.AttributeGroupCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function (item) {
        if (window.getSelection().type == 'Range') return;

        Redirect.AttributeGroupEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function (item) {
        if (!$window.confirm('Удалить?')) return;

        AttributeGroup.delete(item.id).then(function (response) {
            $scope.init()
        });
    };
};