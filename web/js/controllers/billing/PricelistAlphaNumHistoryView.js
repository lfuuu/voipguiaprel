var PricelistAlphaNumHistoryViewCtrl = function($rootScope, $scope, PricelistAlphaNumHistoryItem, params, $modalInstance, Redirect) {
    if (params.id) {
        PricelistAlphaNumHistoryItem.read({a2p_alphanum_history_id: params.id}).then(function (data) {
            $scope.list = data;
        });
    } else {
        $scope.list = [];
    }


    $scope.map = {
        'delete': 'Удаление кода',
        'new': 'Новый код',
    };
    
    $scope.styleMap = {
        'delete': 'font-weight: bold; background-color: lightgrey;',
        'new': 'font-weight: bold; background-color: lightblue;',
    };



    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
