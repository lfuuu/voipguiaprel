var PricelistLocationEditCtrl = function($scope, List, SimImsi, PricelistLocation, Mcc, Mnc, params, $modalInstance, $window) {

    $scope.pricelistIsActive = params.pricelist_is_active;
    $scope.pricelistServiceTypeId = params.pricelist_service_type_id;

    var watchers = {
        mcc: function (newValue, oldValue) {
            if (newValue != oldValue) {
                Mnc.listByMcc({mcc: newValue}).then(function (data) {
                    $scope.mncList = data;
                });
            }
        }
    };

    if (params.id) {
        PricelistLocation.get({id: params.id}).then(function(data){
            $scope.item = data;
            $scope.item.mcc = $scope.item.mcc.replace('{', '').replace('}', '').split(',');
            $scope.item.sim_partner = $scope.item.sim_partner.replace('{', '').replace('}', '').split(',');
            $scope.item.sim_profile = $scope.item.sim_profile.replace('{', '').replace('}', '').split(',');

            Mnc.listByMcc({mcc: $scope.item.mcc}).then(function (data) {
                $scope.mncList = data;
                $scope.item.mnc = $scope.item.mnc.replace('{', '').replace('}', '').split(',');
            });

            $scope.$watch('item.mcc', watchers.mcc);
        });
    } else if (params.pricelist_id) {
        $scope.item = {
            pricelist_id: params.pricelist_id,
            location_id: 1
        };

        $scope.$watch('item.mcc', watchers.mcc);
    } else {
        $scope.item = {
            location_id: 1
        };

        $scope.$watch('item.mcc', watchers.mcc);
    }

    Mcc.list().then(function (result) {
        $scope.mccList = result;
    });

    SimImsi.partner().then(function (result) {
        $scope.partnerList = result;
    });

    SimImsi.profile().then(function (result) {
        $scope.profileList = result;
    });

    $scope.location = List.location();

    $scope.save = function()
    {
        var data = angular.copy($scope.item);

        data.mcc = (typeof data.mcc == 'undefined') ? '{}' : '{' + data.mcc.join(',') + '}';
        data.mnc = (typeof data.mnc == 'undefined') ? '{}' : '{' + data.mnc.join(',') + '}';
        data.sim_partner = (typeof data.sim_partner == 'undefined') ? '{}' : '{' + data.sim_partner.join(',') + '}';
        data.sim_profile = (typeof data.sim_profile == 'undefined') ? '{}' : '{' + data.sim_profile.join(',') + '}';

        PricelistLocation.save(data).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };
};