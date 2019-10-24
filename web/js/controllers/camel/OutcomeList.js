var CamelOutcomeListCtrl = function($scope, CamelOutcome, Redirect, $window) {

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