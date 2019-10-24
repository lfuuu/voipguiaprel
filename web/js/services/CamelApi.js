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

app.factory('CamelTrunk', function ($q, ApiLoader, $rootScope) {
    var url = '/json/camel/trunk/';
    var list = undefined;
    var promise = undefined;
    return basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise);
});

app.factory('CamelGt', function ($q, ApiLoader, $rootScope) {
    var url = '/json/camel/gt/';
    var list = undefined;
    var promise = undefined;
    return basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise);
});

app.factory('CamelRouteTable', function ($q, ApiLoader, $rootScope) {
    var url = '/json/camel/route-table/';
    var list = undefined;
    var promise = undefined;
    return basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise);
});

app.factory('CamelTestGroup', function ($q, ApiLoader, $rootScope) {
    var url = '/json/camel/test-group/';
    var list = undefined;
    var promise = undefined;
    return basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise);
});

app.factory('CamelTestAuth', function ($q, ApiLoader, $rootScope) {
    var url = '/json/camel/test-auth/';
    var list = undefined;
    var promise = undefined;
    var functions = basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise);

    functions.result = function (id) {
        list = undefined;
        return ApiLoader.post(url + 'result', {id: id});
    };

    return functions;
});

app.factory('CamelOutcome', function ($q, ApiLoader, $rootScope) {
    var url = '/json/camel/outcome/';
    var list = undefined;
    var promise = undefined;
    return basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise);
});

app.factory('CamelList', function (CamelTrunk, CamelGt, CamelRouteTable, CamelTestGroup, CamelTestAuth, CamelOutcome) {
    return {
        trunk: function (data) {
            return CamelTrunk.list(data);
        },
        gt: function (data) {
            return CamelGt.list(data);
        },
        routeTable: function (data) {
            return CamelRouteTable.list(data);
        },
        testGroup: function (data) {
            return CamelTestGroup.list(data);
        },
        testAuth: function (data) {
            return CamelTestAuth.list(data);
        },
        outcome: function (data) {
            return CamelOutcome.list(data);
        }
    };
});
