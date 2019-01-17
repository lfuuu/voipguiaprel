var MajorGroupListCtrl = function ($scope, MajorGroup, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'name'
    ];

    $scope.init = function (tab) {
        if (tab) tab.title = 'Группы прайслистов';

        MajorGroup.read().then(function (data) {
            $scope.list = data;
        });
    };

    $scope.clickCreate = function () {
        Redirect.majorGroupCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function (item) {
        if (!userPermissions['major_group_list']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.majorGroupEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function (item) {
        if (!$window.confirm('Удалить?')) return;

        MajorGroup.delete(item.id).then(function (response) {
            $scope.init()
        });
    };
};