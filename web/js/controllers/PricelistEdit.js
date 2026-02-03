var PricelistEditCtrl = function($scope, List, Pricelist, PricelistLocation, params, $modalInstance, $window, MajorGroup) {

    $scope.round_type = [
        {id: 1, name: 'round'},
        {id: 2, name: 'ceil'}
    ];

    if (params.id) {
        Pricelist.get({id: params.id}).then(function(data){
            $scope.item = data;

            PricelistLocation.listByPricelist({'pricelist_id': $scope.item.id}).then(function (data) {
                $scope.locations = data;
            });
            
            $scope.item.nnp_filter = $scope.item.num_c_nnp_filter;
            if ($scope.item.connection_setup_fee_label == null) {
                $scope.item.connection_setup_fee_label = '0';
            }
            if ($scope.item.connection_setup_fee_mav == null) {
                $scope.item.connection_setup_fee_mav = '0';
            }
        });
    } else {
        var date = new Date();

        var dateCreated = date.toISOString().slice(0, 10);
        date.setDate(date.getDate() + 7);
        var dateStart = date.toISOString().slice(0, 10);

        $scope.item = {
            pricelist_version: 1,
            date_created: dateCreated,
            date_start: dateStart,
            date_end: '3000-01-01',
            currency_id: 'RUB',
            is_active: false,
            default_tarification_type: 2,
            minimal_minutes: 0,
            minimal_cost: 0,
            minimum_margin: '0',
            minimum_margin_type: 1,
            connection_setup_fee_label: '0',
            connection_setup_fee_mav: '0',
            nnp_filter: null
        };

        if (params.group_id) {
            $scope.item.pricelist_group_id = params.group_id;
        }
    }

    MajorGroup.read().then(function (data) {
        $scope.list = data.filter(function(item) {
            return item.use_for_c === true;
        });
        
        $scope.filterList = $scope.list.map(function(item) {
            return { id: item.id, name: item.name };
        });
    });

    List.currency().then(function (data) {
        $scope.currency = data;
    });

    List.pricelistGroup().then(function (data) {
        $scope.pricelistGroupList = data;
    });

    Pricelist.list().then(function(data) {
        $scope.additionalPricelistList = data;
    });
    

    $scope.save = function() {
        $scope.item.num_c_nnp_filter = $scope.item.nnp_filter;

        Pricelist.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.saveAndUpdate = function() {
        $scope.item.num_c_nnp_filter = $scope.item.nnp_filter;

        Pricelist.saveAndUpdate($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function() {
        $modalInstance.dismiss();
    };

    $scope.createNewVersion = function() {
        //do_nothing
    };
};
