var CamelGtListCtrl = function($scope, CamelGt, Redirect, $window) {

    $scope.sortType = 'trunk_name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'gt', 'oper', 'country', 'area', 'loc'
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Справочник GT';

        CamelGt.read().then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.camelGtCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['camel_gt_list']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.camelGtEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        CamelGt.delete(item.id).then(function(response) {
            $scope.init()
        });
    };
};