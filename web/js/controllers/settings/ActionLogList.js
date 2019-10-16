var ActionLogListCtrl = function ($scope, ActionLog, List, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.hideFilter = false;
    $scope.isLoading = false;
    $scope.noData = false;

    $scope.filterFields = [
        'user_id', 'controller', 'action', 'request_date', 'object_id'
    ];

    $scope.currentPage = 1;
    $scope.limit = 25;
    $scope.offset = (($scope.currentPage - 1) * $scope.limit);
    $scope.totalItems = 0;

    $scope.timeIntervals = List.timeInterval();

    $scope.init = function (tab) {
        if (tab) tab.title = 'История изменений';

        var date = new Date();
        date.setHours(date.getHours() + 3);

        var dateTo = date.toISOString().slice(0, 19).replace('T', ' ');
        date.setMinutes(date.getMinutes() - 1);
        var dateFrom = date.toISOString().slice(0, 19).replace('T', ' ');

        $scope.searchArray = {
            user_id: 'all',
            controller: 'all',
            action: 'all',
            sort_asc: false,
            is_time_absolute: true,
            time_from: dateFrom,
            time_to: dateTo
        };

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

    ActionLog.getControllerList().then(function (data) {
        $scope.controllerList = data;
    });

    ActionLog.getActionList().then(function (data) {
        $scope.actionList = data;
    });

    $scope.clickSearch = function() {
        $scope.refreshList();
    };

    $scope.clickItem = function (item) {
        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect.actionLogItemView(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.setPagingData = function (page) {
        $scope.offset = ((page - 1) * $scope.limit);
        $scope.refreshList();
    };
};