var ImsiPartnerListCtrl = function($scope, List, ImsiPartner, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'name', 'orig_trunk_name',
        'term_trunk_name', 'mvno_region_name',
        'location_name'
    ];

    $scope.location = List.location();

    $scope.init = function(tab) {
        if (tab) tab.title = 'IMSI партнеры';

        ImsiPartner.read().then(function(data){
            $scope.list = data;

            for (var i in $scope.list) {
                $scope.list[i].location_name = $scope.location.find(function(e) {return e.id == $scope.list[i].location_id;}).name
            }
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