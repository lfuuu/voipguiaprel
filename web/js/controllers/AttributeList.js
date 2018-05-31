var AttributeListCtrl = function ($scope, Attribute, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'name', 'note'
    ];

    $scope.init = function (tab) {
        if (tab) tab.title = 'Attribute';

        Attribute.read().then(function (data) {
            $scope.list = data;
        });
    };

    $scope.clickCreate = function () {
        Redirect.attributeCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function (item) {
        if (!userPermissions['attribute_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.attributeEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function (item) {
        if (!$window.confirm('Удалить?')) return;

        Attribute.delete(item.id).then(function (response) {
            $scope.init()
        });
    };
};