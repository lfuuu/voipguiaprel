var PricelistPrefixPriceEditCtrl = function($scope, List, PricelistPrefixPrice, params, $modalInstance, $window) {
    $scope.errors = [];
    $scope.date_now = new Date();
    $scope.date_one_week = new Date();
    $scope.date_one_week.setDate($scope.date_one_week.getDate() + 7);
    $scope.base_price = 0;
    $scope.base_date_from = '';

    var watchers = {
        b_number_price: function (newValue, oldValue) {
            if ($scope.pricelistIsActive && $scope.item) {
                if (newValue > $scope.base_price) {
                    $scope.item.date_from = $scope.date_one_week.toISOString().slice(0, 10);
                } else if (newValue < $scope.base_price) {
                    $scope.item.date_from = $scope.date_now.toISOString().slice(0, 10);
                } else {
                    $scope.item.date_from = $scope.base_date_from;
                }
            }
        }
    };

    if (params.id) {
        $scope.pricelistIsActive = params.pricelist_is_active;
        $scope.pricelistId = params.pricelist_id;

        PricelistPrefixPrice.get({id: params.id}).then(function(data) {
            $scope.item = data;
            $scope.item.pricelist_is_active = $scope.pricelistIsActive;
            $scope.item.pricelist_id = $scope.pricelistId;

            $scope.base_date_from = data.date_from;
            $scope.base_price = data.b_number_price;
        });

        $scope.$watch('item.b_number_price', watchers.b_number_price);
    } else if (params.filter_b_id) {
        var date = new Date();
        var pricelistDate = new Date(params.pricelist_date_start);
        $scope.pricelistIsActive = params.pricelist_is_active;
        $scope.pricelistId = params.pricelist_id;

        $scope.item = {
            prefix_b: '',
            pricelist_filter_b_id: params.filter_b_id,
            date_from: date > pricelistDate ? date.toISOString().slice(0, 10) : pricelistDate.toISOString().slice(0, 10),
            date_to: '3000-01-01',
            pricelist_is_active: $scope.pricelistIsActive,
            pricelist_id: $scope.pricelistId
        };


    } else {
        var date = new Date();
        var pricelistDate = new Date(params.pricelist_date_start);

        $scope.item = {
            prefix_b: '',
            date_from: date > pricelistDate ? date.toISOString().slice(0, 10) : pricelistDate.toISOString().slice(0, 10),
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