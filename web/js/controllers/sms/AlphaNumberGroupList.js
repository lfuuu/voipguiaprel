var AlphaNumberGroupListCtrl = function($scope, AlphaNumberGroup, Redirect, $window) {
    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'name'
    ];

    $scope.init = function(tab) {
        // if (tab) tab.title = 'Group Альфа номера';
        $scope.refreshList();
    };

    $scope.refreshList = function() {
        AlphaNumberGroup.list().then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.alphaNumberGroupCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        Redirect.alphaNumberGroupEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.copyItem = function (item) {
        if (!$window.confirm('Копировать?')) return;

        AlphaNumberGroup.copy(item.id, item.name).then(function (response) {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        AlphaNumberGroup.delete(item.id).then(function(response) {
            $scope.init()
        });
    };
   
};