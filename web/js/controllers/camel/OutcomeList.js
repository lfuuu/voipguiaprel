var CamelOutcomeListCtrl = function($scope, CamelOutcome, CamelList, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'name', 'type_id'
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Outcomes';

        CamelOutcome.read({server_id: $scope.server.id}).then(function(data){
            $scope.list = data;
        });
    };

    $scope.outcomeTypeList = CamelList.outcomeType();

    $scope.getTypeName = function (typeId) {
        for (var i in $scope.outcomeTypeList) {
            if ($scope.outcomeTypeList[i]['id'] == typeId) {
                return $scope.outcomeTypeList[i]['name'];
            }
        }
    };

    $scope.clickCreate = function() {
        Redirect.camelOutcomeCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['camel_outcome_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.camelOutcomeEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        CamelOutcome.delete(item.id).then(function(response) {
            $scope.init()
        });
    };
};