app.factory('ApiLoader', function ($q, $http, $rootScope) {
	$rootScope.isAjaxLoading = false;
	$rootScope.popupErrors = false;
	var loaderCount = 0;
	var incLoaderCount = function() {
		if (++loaderCount == 1) {
			$rootScope.isAjaxLoading = true;
		}
	}
	var decLoaderCount = function() {
		if (--loaderCount == 0) {
			$rootScope.isAjaxLoading = false;
		}
	}
	return {
		post: function(url, requestData) {
			var deferred = $q.defer();

			$http.post(url, requestData)
				.success(function(responseData, status, headers, config) {
					if (responseData && responseData.errors) {
						deferred.reject(responseData.errors);
					}
					deferred.resolve(responseData);

					$rootScope.popupErrors = false;
					decLoaderCount();
				})
				.error(function(responseData, status, headers, config) {
					deferred.reject([{http: [ status + '. ' + config.method + ' ' + config.url ]}]);
					$rootScope.popupErrors = {
						header: false,
						errors: []
					}

					if (responseData.type == 'app\\exceptions\\FormValidationException') {
						$rootScope.popupErrors.header = 'Не верные параметры запроса';
						for (var i in responseData.errors) {
							for (var n in responseData.errors[i]) {
								$rootScope.popupErrors.errors.push(responseData.errors[i][n]);
							}
						}
					} else if (responseData.type == 'yii\\web\\ForbiddenHttpException') {
						$rootScope.popupErrors.header = 'Отказано в доступе';
					} else {
						$rootScope.popupErrors.header = 'Ошибка ' + status;
						if (responseData.message) {
							$rootScope.popupErrors.errors.push(responseData.message);
						}
					}

					decLoaderCount();
				})
			;
			incLoaderCount();
			return deferred.promise;
		}
	}
});
