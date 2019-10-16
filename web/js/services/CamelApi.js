function basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise) {
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        list: function () {
            if (promise !== undefined) return promise;

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                var data = {};
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

app.factory('CamelList', function (CamelTrunk, CamelGt) {
    return {
        trunk: function () {
            return CamelTrunk.list();
        },
        gt: function () {
            return CamelGt.list();
        }
    };
});
