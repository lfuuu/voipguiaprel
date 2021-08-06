var SmsTestGroupListCtrl = function($scope, SmsTestGroup, Redirect, $window) {

    $scope.sortType = 'group_name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'group_name'
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Группы тестов';

        SmsTestGroup.read().then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.smsTestGroupCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['sms_test_group_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.smsTestGroupEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        SmsTestGroup.delete(item.id).then(function(response) {
            $scope.init()
        });
    };
};