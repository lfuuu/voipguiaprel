app.factory('ApiLoader', function ($q, $http, $rootScope) {
    $rootScope.isAjaxLoading = false;
    $rootScope.popupErrors = false;

    var loaderCount = 0;

    var incLoaderCount = function() {
        if (++loaderCount == 1) {
            $rootScope.isAjaxLoading = true;
        }
    };

    var decLoaderCount = function() {
        if (--loaderCount == 0) {
            $rootScope.isAjaxLoading = false;
        }
    };

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
                    var rejectionPayload = [{
                        http: [ status + '. ' + config.method + ' ' + config.url ]
                    }];
                    rejectionPayload[0].data = responseData;

                    $rootScope.popupErrors = {
                        header: false,
                        errors: []
                    };

                    var collectMessages = function(messages) {
                        if (!messages) return;
                        if (!angular.isArray(messages)) {
                            messages = [messages];
                        }
                        angular.forEach(messages, function(message) {
                            if (message === undefined || message === null) return;
                            var text = ('' + message).replace(/\s+$/, '');
                            if (text.length) {
                                $rootScope.popupErrors.errors.push(text);
                            }
                        });
                    };

                    var extractedMessages = [];
                    var responseType = responseData && responseData.type;
                    var responseObject = angular.isObject(responseData) ? responseData : {};

                    if (responseType == 'app\\exceptions\\FormValidationException') {
                        $rootScope.popupErrors.header = 'Не верные параметры запроса';
                        for (var i in responseObject.errors) {
                            for (var n in responseObject.errors[i]) {
                                $rootScope.popupErrors.errors.push(responseObject.errors[i][n]);
                                }
                            }
                    } else if (angular.isString(responseData)) {
                        $rootScope.popupErrors.header = 'Ошибка ' + status;
                        extractedMessages.push(responseData);
                    } else if (responseType == 'yii\\web\\ForbiddenHttpException') {
                        $rootScope.popupErrors.header = 'Отказано в доступе';
                    } else {
                        $rootScope.popupErrors.header = 'Ошибка ' + status;
                        if (responseObject.message) {
                            extractedMessages.push(responseObject.message);
                        } else if (responseObject.error) {
                            extractedMessages.push(responseObject.error);
                        } else if (responseObject.text) {
                            extractedMessages.push(responseObject.text);
                        }
                    }

                    if (!$rootScope.popupErrors.errors.length) {
                        collectMessages(extractedMessages);
                    }

                    if (!$rootScope.popupErrors.errors.length) {
                        collectMessages('Неизвестная ошибка');
                    }

                    if (extractedMessages.length) {
                        rejectionPayload[0].message = extractedMessages.join('\n');
                    }

                    deferred.reject(rejectionPayload);
                    decLoaderCount();
                })
            ;
            incLoaderCount();
            return deferred.promise;
        }
    }
});