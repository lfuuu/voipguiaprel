var HeaderRuleListCtrl = function ($scope, HeaderRule, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'name', 'description', 'value'
    ];

    $scope.init = function (tab) {
        if (tab) tab.title = 'Header Rule';

        HeaderRule.read({server_id: $scope.server.id}).then(function (data) {
            $scope.list = data;
        });
    };

    $scope.clickCreate = function () {
        Redirect.headerRuleCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function (item) {
        if (!userPermissions['header_rule_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.headerRuleEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function (item) {
        if (!$window.confirm('Удалить?')) return;

        HeaderRule.delete(item.id).then(function (response) {
            $scope.init()
        });
    };
};