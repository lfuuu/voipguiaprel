var PricelistEditCtrl = function($scope, List, Pricelist, PricelistLocation, params, $modalInstance, $window) {

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
            minimal_cost: 0
        };

        if (params.group_id) {
            $scope.item.pricelist_group_id = params.group_id;
        }
    }

    $scope.currency = List.currency();

    List.pricelistGroup().then(function (data) {
        $scope.pricelistGroupList = data;
    });

    $scope.save = function()
    {
        Pricelist.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.saveAndUpdate = function()
    {
        Pricelist.saveAndUpdate($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };

    $scope.createNewVersion = function()
    {
        //do_nothing
    };
};