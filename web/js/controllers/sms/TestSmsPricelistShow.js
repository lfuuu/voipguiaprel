var TestSmsPricelistShowTestCtrl = function($scope, TestSmsPricelist, Redirect, params, $modalInstance) {

  // рекурсивная функция: переименовывает nodes -> steps
  function normalizeNode(node) {
    if (!node || typeof node !== 'object') return node;

    // если есть trace внутри узла — тоже обрабатываем его
    if (node.trace && typeof node.trace === 'object' && Array.isArray(node.trace.nodes)) {
      node.name = node.trace.name || node.name || 'trace';
      node.steps = node.trace.nodes.map(normalizeNode);
      delete node.trace;
    }

    // если обычный узел с nodes
    if (Array.isArray(node.nodes)) {
      node.steps = node.nodes.map(normalizeNode);
      delete node.nodes;
    }

    return node;
  }

  function wrapTraceToSteps(data) {
    if (!data) return data;

    // если верхний уровень содержит trace
    if (data.trace && Array.isArray(data.trace.nodes)) {
      var root = normalizeNode({
        name: data.trace.name || 'Start NNP Calc calculation',
        nodes: data.trace.nodes,
        match: data.match,
        fix_price: data.fix_price,
        rate_price: data.rate_price,
        interconnect_price: data.interconnect_price
      });
      data.steps = [root];
      delete data.trace;
    }

    // если массив steps уже есть — прогоняем рекурсивно
    if (Array.isArray(data.steps)) {
      data.steps = data.steps.map(normalizeNode);
    }

    return data;
  }

  // --- UI helpers
  $scope.collapseAll = function() {
    $scope.$broadcast('angular-ui-tree:collapse-all');
  };

  $scope.back = function() {
    $modalInstance.dismiss();
  };

  // --- загрузка данных
  $scope.item = null;
  $scope.url = '';
  $scope.baseUrl = '';

  if (params && params.id) {
    TestSmsPricelist.result({ id: params.id }).then(function(data) {
      data = wrapTraceToSteps(data);
      $scope.item = data;
      $scope.url = data.url;
      $scope.baseUrl = data.baseUrl;
    });
  }
};
