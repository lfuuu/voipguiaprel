function basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise) {
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        list: function (data) {
            if (promise !== undefined) return promise;

            if (!data.server_id) {
                data.server_id = $rootScope.server.id;
            }

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                data = data || {};
                ApiLoader.post(url + 'list', data)
                    .then(function (data) {
                        list = data;
                        promise = undefined;
                        deferred.resolve(data);
                    }, function (data) {
                        promise = undefined;
                        deferred.reject(data);
                    });
                promise = deferred.promise;
            }
            return deferred.promise;
        },
        save: function (data) {
            list = undefined;
            return ApiLoader.post(url + 'save', data);
        },
        delete: function (id) {
            list = undefined;
            return ApiLoader.post(url + 'delete', {id: id});
        }
    };
};

app.factory('ApiBillingApi', function ($q, ApiLoader, $rootScope) {
    var url = '/json/api_billing/api/';
    var list = undefined;
    var promise = undefined;
    return basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise);
});

app.factory('ApiBillingApiMethod', function ($q, ApiLoader, $rootScope) {
    var url = '/json/api_billing/api-method/';
    var list = undefined;
    var promise = undefined;
    return basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise);
});

app.factory('ApiBillingApiPricelist', function ($q, ApiLoader, $http, $rootScope) {
    var url = '/json/api_billing/api-pricelist/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
         exportToExcel: function(data) {
        return $http.post(url + 'export-to-excel', data, { responseType: 'arraybuffer' });
    },
        readArchive: function (data) {
            return ApiLoader.post(url + 'read-archive', data);
        },
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        list: function (data) {
            if (promise !== undefined) return promise;

            if (!data.server_id) {
                data.server_id = $rootScope.server.id;
            }

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                data = data || {};
                ApiLoader.post(url + 'list', data)
                    .then(function (data) {
                        list = data;
                        promise = undefined;
                        deferred.resolve(data);
                    }, function (data) {
                        promise = undefined;
                        deferred.reject(data);
                    });
                promise = deferred.promise;
            }
            return deferred.promise;
        },
        save: function (data) {
            list = undefined;
            return ApiLoader.post(url + 'save', data);
        },
        delete: function (id) {
            list = undefined;
            return ApiLoader.post(url + 'delete', {id: id});
        },
        copy: function (id) {
            return ApiLoader.post(url + 'copy', { id: id });
        },
        copyAndMultiply: function (id, multiplier) {
            return ApiLoader.post(url + 'copy-and-multiply', { id: id, multiplier: multiplier });
        },
        restore: function(id) {
            list = undefined;
            return ApiLoader.post(url + 'restore', {id: id});
        }
    }
});

app.factory('ApiBillingApiPricelistItem', function ($q, ApiLoader, $rootScope) {
    var url = '/json/api_billing/api-pricelist-item/';
    var list = undefined;
    var promise = undefined;
    return basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise);
});

app.factory('ApiBillingList', function (ApiBillingApi, ApiBillingApiMethod, ApiBillingApiPricelist) {
    return {
        api: function (data) {
            return ApiBillingApi.list(data);
        },
        apiMethod: function (data) {
            return ApiBillingApiMethod.list(data);
        },
        apiPricelist: function (data) {
            return ApiBillingApiPricelist.list(data);
        },
    };
});
