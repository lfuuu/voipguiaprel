var ActionLogListCtrl = function ($scope, ActionLog, List, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.hideFilter = false;
    $scope.isLoading = false;
    $scope.noData = false;

    $scope.searchArray = {
        user_id: 'all',
    };

    $scope.filterFields = [
        'id', 'name', 'currency_id', 'group_name', 'date_created'
    ];

    $scope.currentPage = 1;
    $scope.limit = 25;
    $scope.offset = (($scope.currentPage - 1) * $scope.limit);
    $scope.totalItems = 0;

    $scope.init = function (tab) {
        if (tab) tab.title = 'История изменений';

        $scope.refreshList();
    };

    $scope.refreshList = function() {
        $scope.isLoading = true;
        $scope.noData = false;
        ActionLog.read({
            search_array: $scope.searchArray,
            offset: $scope.offset,
            limit: $scope.limit
        }).then(function (data) {
            $scope.isLoading = false;
            $scope.list = data.data;
            $scope.totalItems = data.totalCount;
        });
    };

    List.user().then(function (data) {
        $scope.userList = data;
    });

    $scope.clickSearch = function() {
        $scope.refreshList();
    };

    $scope.setPagingData = function (page) {
        $scope.offset = ((page - 1) * $scope.limit);
        $scope.refreshList();
    };
};