var app = angular.module('app', ['ui.bootstrap', 'ui.select2', 'ui.sortable']);
app.run(function($rootScope, $templateCache){
	$rootScope.tabs = [];



	for (var t in window.templates) {
		$templateCache.put(t, window.templates[t]);
	}
	delete window.templates;
});






















































