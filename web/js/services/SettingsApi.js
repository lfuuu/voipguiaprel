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
        delete: function (data) {
            list = undefined;
            return ApiLoader.post(url + 'delete', data);
        }
    };
};

app.factory('Acl', function ($q, ApiLoader, $rootScope) {
    var url = '/json/settings/acl/';
    var list = undefined;
    var promise = undefined;
    return basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise);
});

app.factory('Role', function ($q, ApiLoader, $rootScope) {
    var url = '/json/settings/role/';
    var list = undefined;
    var promise = undefined;
    return basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise);
});

app.factory('User', function ($q, ApiLoader, $rootScope) {
    var url = '/json/settings/user/';
    var list = undefined;
    var promise = undefined;
    return basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise);
});

app.factory('SettingsList', function (Acl, Role) {
    return {
        acl: function () {
            return Acl.list();
        },
        role: function () {
            return Role.list();
        },
        user: function () {
            return User.list();
        }
    };
});
app.factory('GlobalSettings', function($q, ApiLoader, $rootScope) {
    var url = '/json/settings/global-settings/';
    return {
        get: function() {
            return ApiLoader.post(url + 'get');
        },
        update: function(data) {
            return ApiLoader.post(url + 'update', data);
        },
        callProcedure: function(params) {
            // Предполагается, что на сервере реализован экшен call-procedure,
            // который принимает параметр procedure и выполняет вызов хранимой процедуры.
            return ApiLoader.post(url + 'call-procedure', params);
        }
    };
});



