var AlphaNumberListCtrl = function($scope, AlphaNumber, Redirect, $window) {


    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'name'
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Альфа номера';
        $scope.refreshList();
    };

    $scope.refreshList = function() {
        AlphaNumber.list().then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.alphaNumberCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        Redirect.alphaNumberEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        AlphaNumber.delete(item.id).then(function(response) {
            $scope.init()
        });
    };
   
};