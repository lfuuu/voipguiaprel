var PricelistPrefixPriceEditCtrl = function($scope, List, PricelistPrefixPrice, params, $modalInstance, $window) {
    $scope.errors = [];

    if (params.id) {
        $scope.pricelistIsActive = params.pricelist_is_active;

        PricelistPrefixPrice.get({id: params.id}).then(function(data){
            $scope.item = data;
        });
    } else if (params.filter_b_id) {
        var date = new Date();
        var pricelistDate = new Date(params.pricelist_date_start);
        $scope.pricelistIsActive = params.pricelist_is_active;

        $scope.item = {
            pricelist_filter_b_id: params.filter_b_id,
            date_from: pricelistDate.toISOString().slice(0, 10),
            date_to: '3000-01-01'
        };
    } else {
        var date = new Date();
        var pricelistDate = new Date(params.pricelist_date_start);

        $scope.item = {
            date_from: pricelistDate.toISOString().slice(0, 10),
            date_to: '3000-01-01'
        };
    }

    $scope.save = function()
    {
        PricelistPrefixPrice.save($scope.item).then(function(response) {
            if (response.error) {
                $scope.displayError(response);
                return;
            }

            $modalInstance.close();
        });
    };

    $scope.displayError = function(response)
    {
        $scope.errors[response.field + '_error'] = response.error;
    };
    
    $scope.isDateBeforeNow = function() {
        if (!$scope.item.id) {
            return false;
        }

        var now = new Date();
        var prefixDate = new Date($scope.item.date_from);

        if (prefixDate <= now) {
            return true;
        }

        return false;
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };
};