var ImsiPartnerListCtrl = function($scope, ImsiPartner, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.init = function(tab) {
        if (tab) tab.title = 'IMSI партнеры';

        ImsiPartner.read().then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.imsiPartnerCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['imsi_partner_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.imsiPartnerEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        ImsiPartner.delete(item.id).then(function(response) {
            $scope.init()
        });
    };
};