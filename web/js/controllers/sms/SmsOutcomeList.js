var SmsOutcomeListCtrl = function($scope, SmsOutcome, SmsList, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'name', 'type_id'
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Sms Outcomes';

        SmsOutcome.read({server_id: $scope.server.id}).then(function(data){
            $scope.list = data;
        });
    };

    $scope.outcomeTypeList = SmsList.outcomeType();

    $scope.getTypeName = function (typeId) {
        for (var i in $scope.outcomeTypeList) {
            if ($scope.outcomeTypeList[i]['id'] == typeId) {
                return $scope.outcomeTypeList[i]['name'];
            }
        }
    };

    $scope.clickCreate = function() {
        Redirect.smsOutcomeCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['camel_outcome_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.smsOutcomeEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        SmsOutcome.delete(item.id).then(function(response) {
            $scope.init()
        });
    };
};