var MajorListCtrl = function ($scope, Major, MajorGroup, Nnp, List, Redirect, $window) {
    $scope.countryCode = '643';
    $scope.groupId = 'undefined';

    $scope.init = function (tab) {
        if (tab) tab.title = 'Мэджор';

        $scope.refreshList();
    };

    $scope.refreshList = function() {
        if ($scope.countryCode == 'undefined' && $scope.groupId == 'undefined') {
            return;
        }

        Major.read({
            country_code: $scope.countryCode,
            group_id: $scope.groupId
        }).then(function (data) {
            $scope.list = data;
        });
    };

    Nnp.countryList().then(function (data) {
        $scope.countryList = data;
    });

    MajorGroup.list().then(function (data) {
        $scope.groupList = data;
    });

    $scope.countryChanged = function(countryCode) {
        $scope.countryCode = countryCode;

        $scope.refreshList();
    };

    $scope.groupChanged = function(groupId) {
        $scope.groupId = groupId;

        $scope.refreshList();
    };

    $scope.clickCreate = function () {
        Redirect.majorCreate($scope.countryCode, $scope.list.length).then(function () {
            $scope.init();
        });
    };

    $scope.move = function (id, direction) {
        Major.move(id, direction).then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function (item) {
        if (!userPermissions['major_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.majorEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.testItem = function (item) {
        var factor;

        factor = parseFloat($window.prompt('Введите фактор', 10));

        if (isNaN(factor) || factor <= 0) return;

        Redirect.majorTest(item.id, factor).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function (item) {
        if (!$window.confirm('Удалить?')) return;

        Major.delete(item.id).then(function (response) {
            $scope.init()
        });
    };
};