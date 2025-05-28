(function () {
  app.directive('actionLogViewButton', actionLogViewButton);

  actionLogViewButton.$inject = ['Redirect'];

  function actionLogViewButton(Redirect) {
    return {
      restrict: 'E',
      templateUrl: '/templates/directives/action-log-view-button.html',
      scope: {
        key:     '@',
        type:    '@',
        comment: '=',
        action:  '@?'
      },
      link: function(scope) {
        scope.viewActionLog = function() {
          Redirect
            .actionLogView(scope.key, scope.type, scope.action)
            .then(function(response) {
            });
        };
      }
    };
  }
})();
