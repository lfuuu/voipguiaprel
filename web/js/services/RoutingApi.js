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

app.factory('TestDial', function ($q, ApiLoader, $rootScope) {
    var url = '/json/routing/test-dial/';
    var list = undefined;
    var promise = undefined;
    var functions = basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise);

    functions.call = function (id) {
        return ApiLoader.post(url + 'call', {id: id});
    };

    functions.getForCdr = function (id) {
        return ApiLoader.post(url + 'get-for-cdr', {id: id});
    };

    return functions;
});

app.factory('RoutingList', function (TestDial) {
    return {
        testDial: function (data) {
            return TestDial.list(data);
        }
    };
});
