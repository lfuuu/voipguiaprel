var TestPricelistListCtrl = function($scope, TestPricelist, Scripts, List, Redirect, $window) {
    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.currentPage = 1;
    $scope.limit = 15;
    $scope.offset = (($scope.currentPage - 1) * $scope.limit);
    $scope.totalItems = 0;

    $scope.hideFilter = false;
    $scope.filterFields = [
        'id', 'name', 'pricelist_name', 'a_number',
        'b_number', 'location_name', 'mcc',
        'mnc'
    ];

    $scope.searchArray = {
        id: '',
        name: '',
        group_id: '',
        result: '',
        server_id: '',
        pricelist_id: ''
    };

    $scope.init = function (tab) {
        if (tab) tab.title = 'Test pricelist';

        $scope.refreshList();
    };

    $scope.locationList = List.location();

    $scope.refreshList = function() {
        TestPricelist.read({
            search_array: $scope.searchArray,
            offset: $scope.offset,
            limit: $scope.limit
        }).then(function (data) {
            $scope.list = data.data;
            $scope.totalItems = data.totalCount;
        });
    };

    List.testPricelistGroup().then(function (data) {
        $scope.testGroupList = data;
    });

    List.server().then(function (data) {
        $scope.serverList = data;
    });

    List.pricelist().then(function (data) {
        $scope.pricelistList = data;
    });

    $scope.testResultList = List.testResult();

    $scope.clickSearch = function() {
        $scope.refreshList();
    };

    $scope.clickCreate = function () {
        Redirect.testPricelistCreate().then(function () {
            $scope.init();
        });
    };

    $scope.testGroupChanged = function(testGroupId) {
        $scope.testGroupId = testGroupId;

        $scope.refreshList();
    };

    $scope.testResultChanged = function(testResult) {
        $scope.testResult = testResult;

        $scope.refreshList();
    };

    $scope.clickItem = function (item) {
        if (!userPermissions['test_pricelist_list']) {
            return;
        }

        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect.testPricelistEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.cloneTest = function(item) {
        if (!userPermissions['test_pricelist_create']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.testPricelistClone(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.showTestPrimary = function (item) {
        $scope.showTestBasic(item, 'testPricelistShowTest');
    };

    $scope.showTestBasic = function (item, method) {
        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect[method](item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function (item) {
        if (!$window.confirm('Удалить?')) {
            return;
        }

        TestPricelist.delete(item.id).then(function () {
            $scope.init()
        });
    };

    $scope.setPagingData = function (page) {
        $scope.offset = ((page - 1) * $scope.limit);
        $scope.refreshList();
    };
};