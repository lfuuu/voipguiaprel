var SmsTestAuthEditCtrl = function(
    $rootScope,
    $scope,
    Redirect,
    SmsTestAuth,
    List,
    params,
    $modalInstance,
    SmsList,
    SmsGate,
    SmsTrunk
) {

    $scope.gateList      = [];
    $scope.trunkList     = [];
    $scope.trunkFiltered = [];

    SmsTrunk.read({}).then(function(data){
      $scope.trunkList     = data;
      $scope.trunkFiltered = data.slice();
    });

    SmsList.testGroup({}).then(d => $scope.testGroupList = d);
    List.serverOcs().then(d => $scope.serverList    = d);

    if (params.id) {
      SmsTestAuth.get({id: params.id}).then(function(data){
        $scope.item = data;
        if (params.clone) delete $scope.item.id;
        loadGates($scope.item.server_id);
      });
    } else {
      $scope.item = { name: '', server_id: $scope.server.id };
      loadGates($scope.item.server_id);
    }

    function loadGates(serverId) {
      SmsGate.read({ server_id: serverId })
        .then(function(data){
          $scope.gateList = data;
          if ($scope.item.gate_id) {
            loadTrunksByGate($scope.item.gate_id, /*initial=*/true);
          }
        });
    }

    function loadTrunksByGate(gateId, initial) {
      var promise;
      if (!gateId) {
        promise = Promise.resolve($scope.trunkList.slice());
      } else {
        promise = SmsTrunk.readByGate({ sms_gate_id: gateId });
      }

      promise.then(function(list){
        $scope.trunkFiltered = list;

        if (initial && $scope.item.trunk_name) {
          var exists = $scope.trunkFiltered.some(function(t){
            return t.name === $scope.item.trunk_name;
          });
          if (!exists) {
            $scope.trunkFiltered.unshift({ name: $scope.item.trunk_name });
          }
        }

      });
    }

    $scope.$watch('item.gate_id', function(n, o){
      if (n !== o) {
        loadTrunksByGate(n, /*initial=*/false);
      }
    });

    $scope.save = function(){
      SmsTestAuth.save($scope.item).then(function(){
        $modalInstance.close();
      });
    };
    $scope.back = function(){
      $modalInstance.dismiss();
    };
  }