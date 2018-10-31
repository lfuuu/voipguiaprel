var PricelistListCtrl = function ($scope, Pricelist, List, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'name', 'currency_id', 'group_name', 'date_created'
    ];

    $scope.groupId = 'undefined';

    $scope.currentPage = 1;
    $scope.limit = 15;
    $scope.offset = (($scope.currentPage - 1) * $scope.limit);
    $scope.totalItems = 0;

    $scope.init = function (tab) {
        if (tab) tab.title = 'Pricelist';

        $scope.refreshList();
    };

    $scope.refreshList = function() {
        if ($scope.groupId == 'undefined') {
            return;
        }

        Pricelist.read({
            group_id: $scope.groupId,
            offset: $scope.offset,
            limit: $scope.limit
        }).then(function (data) {
            $scope.list = data.data;
            $scope.totalItems = data.totalCount;
        });
    };

    List.pricelistGroup().then(function (data) {
        $scope.groupList = data;
    });

    $scope.groupChanged = function(groupId) {
        $scope.groupId = groupId;

        $scope.refreshList();
    };

    $scope.clickCreate = function () {
        Redirect.pricelistCreate($scope.groupId).then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function (item) {
        if (!userPermissions['pricelist_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.pricelistShortView(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.copyItem = function (item) {
        if (!$window.confirm('Копировать?')) return;

        Pricelist.copy(item.id).then(function (response) {
            $scope.init();

            Redirect.pricelistShortView(response.id).then(function () {
                $scope.init();
            });
        });
    };

    $scope.deleteItem = function (item) {
        if (!$window.confirm('Удалить?')) return;

        Pricelist.delete(item.id).then(function (response) {
            $scope.init()
        });
    };

    $scope.setPagingData = function (page) {
        $scope.offset = ((page - 1) * $scope.limit);
        $scope.refreshList();
    };
};