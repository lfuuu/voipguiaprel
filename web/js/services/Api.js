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

app.factory('Attribute', function ($q, ApiLoader, $rootScope) {
    var url = '/json/attribute/';
    var list = undefined;
    var promise = undefined;
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
                var data = {server_id: $rootScope.server.id};
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
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('SmsTrunk', function ($q, ApiLoader, $rootScope) {
    var url = '/json/sms/sms/';
    var list = undefined;
    var promise = undefined;
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
        listByServer: function (server_id) {
            var data = { server_id: $rootScope.server.id };
            console.log(data);
            return ApiLoader.post(url + 'list', data);
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
});

app.factory('SmsRouteTable', function ($q, ApiLoader, $rootScope) {
    var url = '/json/sms/sms-route-table/';
    var list = undefined;
    var promise = undefined;
    return basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise);
});

app.factory('SmsOutcome', function ($q, ApiLoader, $rootScope) {
    var url = '/json/sms/sms-outcome/';
    var list = undefined;
    var promise = undefined;
    return basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise);
});

app.factory('SmsList', function (SmsTrunk, SmsRouteTable, SmsOutcome, SmsTestGroup) {
    return {
        trunk: function (data) {
            return SmsTrunk.list(data);
        },
        routeTable: function (data) {
            return SmsRouteTable.list(data);
        },
        outcome: function (data) {
            return SmsOutcome.list(data);
        },
        testGroup: function (data) {
            return SmsTestGroup.list(data);
        },
        testAuth: function (data) {
            return SmsTestAuth.list(data);
        },
        trunkByServer: function (serverId) {
            return SmsTrunk.listByServer(serverId);
        },
        outcomeType: function () {
            return [
                { id: 1, name: 'ACCEPT' },
                { id: 2, name: 'REJECT' },
                { id: 3, name: 'TEMP NAME' },
            ];
        }
    };
});

app.factory('SmsTestAuth', function ($q, ApiLoader, $rootScope) {
    var url = '/json/sms/test-auth/';
    var list = undefined;
    var promise = undefined;
    var functions = basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise);

    functions.result = function (data) {
        list = undefined;
        return ApiLoader.post(url + 'result', data);
    };
    
    functions.descend = function (data) {
        list = undefined;
        return ApiLoader.post(url + 'descend', data);
    };

    return functions;
});

app.factory('SmsTestGroup', function ($q, ApiLoader, $rootScope) {
    var url = '/json/sms/test-group/';
    var list = undefined;
    var promise = undefined;
    return basicApiFunctions($q, ApiLoader, $rootScope, url, list, promise);
});


app.factory('AttributeGroup', function ($q, ApiLoader, $rootScope) {
    var url = '/json/attribute-group/';
    var list = undefined;
    var promise = undefined;
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
                var data = {server_id: $rootScope.server.id};
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
});

app.factory('Trunk', function ($q, ApiLoader, $rootScope) {
    var
        url = '/json/trunk/',
        list = undefined,
        promise = undefined;

    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        readMarketplace: function (data) {
            return ApiLoader.post(url + 'read-marketplace', data);
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
                var data = { server_id: $rootScope.server.id };
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
        listByServer: function (server_id) {
            var data = { server_id: server_id };
            return ApiLoader.post(url + 'list', data);
        },
        listByServerWithContract: function (server_id) {
            var data = { server_id: server_id };
            return ApiLoader.post(url + 'list-with-contract', data);
        },
        listNameAndAlias: function (hub_id) {
            var data = { hub_id: hub_id };
            return ApiLoader.post(url + 'list-name-and-alias', data);
        },
        save: function (data) {
            list = undefined;
            return ApiLoader.post(url + 'save', data);
        },
        delete: function (id) {
            list = undefined;
            return ApiLoader.post(url + 'delete', { id: id });
        },
        serviceTrunks: function (trunkId) {
            return ApiLoader.post(url + 'get-service-trunks', { 'trunk_id': trunkId });
        },
        listRoaming: function (servers) {
            return ApiLoader.post(url + 'list-roaming', { 'servers': servers });
        },
        toggleAutorouting: function (trunkId, on) {
            return ApiLoader.post(url + 'toggle-autorouting', { 'trunk_id': trunkId, 'on': on });
        },
        findUsagesInTrunkGroups: function (id) {
            return ApiLoader.post(url + 'find-usages-in-trunk-groups', { 'id': id });
        }
    };
});

app.factory('TrunkGroup', function ($q, ApiLoader, $rootScope) {
    var
        url = '/json/trunk-group/',
        list = undefined,
        promise = undefined;

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
                var data = { server_id: $rootScope.server.id };
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
        listForMarketplace: function (serverId) {
            if (!serverId && promise !== undefined) return promise;

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                if (!serverId) {
                    serverId = $rootScope.server.id;
                }
                var data = { server_id: serverId };
                ApiLoader.post(url + 'list-for-marketplace', data)
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
        findIntoRules: function (data) {
            return ApiLoader.post(url + 'get-trunks-with-group-into-rules', data);
        },
        findIntoPriorities: function (data) {
            return ApiLoader.post(url + 'get-trunks-with-group-into-priorities', data);
        },
        findRouteTablesWithGroup: function (data) {
            return ApiLoader.post(url + 'get-route-tables-with-group', data);
        },
        findOutcomesWithGroup: function (data) {
            return ApiLoader.post(url + 'get-outcomes-with-group', data);
        },
        findGroupsWithGroup: function (data) {
            return ApiLoader.post(url + 'get-groups-with-group', data);
        },
        findRouteReplaceWithGroup: function (data) {
            return ApiLoader.post(url + 'get-route-replace-with-group', data);
        },
        save: function (data) {
            list = undefined;
            return ApiLoader.post(url + 'save', data);
        },
        delete: function (id) {
            list = undefined;
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});


app.factory('ServiceTrunkRouting', function ($q, ApiLoader, $rootScope) {
    var
        url = '/json/service-trunk-routing/';

    return {
        save: function (data) {
            return ApiLoader.post(url + 'save', data);
        }
    };
});

app.factory('Prefixlist', function ($q, ApiLoader, $rootScope) {
    var url = '/json/prefixlist/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        listBlocked: function (data) {
            return ApiLoader.post(url + 'list-blocked', data);
        },
        list: function () {
            if (promise !== undefined) return promise;

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                var data = { server_id: ($rootScope.isSms ? 99 : ($rootScope.server.id ? $rootScope.server.id : $rootScope.serverId))};
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
        listByType: function (data) {
            return ApiLoader.post(url + 'list-by-type', data);
        },
        save: function (data) {
            list = undefined;
            return ApiLoader.post(url + 'save', data);
        },
        delete: function (id) {
            list = undefined;
            return ApiLoader.post(url + 'delete', { id: id });
        },
        nnpCalculation: function (id) {
            return ApiLoader.post(url + 'nnp-calculation', { id: id });
        },
        applyBuffer: function (id) {
            return ApiLoader.post(url + 'apply-buffer', { id: id });
        },
        generatePrefixlist: function (id, type) {
            return ApiLoader.post(url + 'prefixlist-generation', { id: id, type: type });
        },
        findUsagesInNumbers: function (id) {
            return ApiLoader.post(url + 'find-usages-in-numbers', { id: id });
        },
        findUsagesInTrunkABRules: function (id) {
            return ApiLoader.post(url + 'find-usages-in-trunk-a-b-rules', { id: id });
        }
    };
});

app.factory('ActionLog', function ($q, ApiLoader, $rootScope) {
    var url = '/json/action-log/';
    return {
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        getOne: function (data) {
            return ApiLoader.post(url + 'get-one', data);
        },
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        getControllerList: function (data) {
            return ApiLoader.post(url + 'get-controller-list', data);
        },
        getActionList: function (data) {
            return ApiLoader.post(url + 'get-action-list', data);
        },
    };
});

app.factory('User', function ($q, ApiLoader, $rootScope) {
    var url = '/json/user/';
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
    };
});

app.factory('OcaBw', function ($q, ApiLoader, $rootScope) {
    var
        url = '/json/oca-bw/',
        list = undefined,
        promise = undefined;

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
                var data = { server_id: $rootScope.server.id };
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
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('Uplink', function ($q, ApiLoader, $rootScope) {
    var url = '/json/uplink/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function () {
            return ApiLoader.post(url + 'read', { as_tree: false });
        },
        readTree: function () {
            return ApiLoader.post(url + 'read', { as_tree: true });
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
                ApiLoader.post(url + 'list')
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
            return ApiLoader.post(url + 'save', data);
        },
        delete: function (id, level) {
            return ApiLoader.post(url + 'delete', { id: id, level: level });
        }
    };
});

app.factory('Billing', function (ApiLoader) {
    var url = '/json/billing/';
    return {
        countries: function () {
            return ApiLoader.post(url + 'countries');
        },
        regions: function () {
            return ApiLoader.post(url + 'regions');
        },
        cities: function (geo) {
            return ApiLoader.post(url + 'cities', geo);
        },
        operators: function () {
            return ApiLoader.post(url + 'operators');
        },
        networkTypes: function () {
            return ApiLoader.post(url + 'network-types');
        }
    };
});

app.factory('RouteCase', function ($q, ApiLoader, $rootScope) {
    var url = '/json/route-case/';
    var list = undefined;
    var promise = undefined;
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
                var data = { server_id: $rootScope.server.id };
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
            return ApiLoader.post(url + 'delete', { id: id });
        },
        findUsagesInOutcomes: function (id) {
            return ApiLoader.post(url + 'find-usages-in-outcomes', { id: id });
        }
    };
});


app.factory('Outcome', function ($q, ApiLoader, $rootScope) {
    var url = '/json/outcome/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        list: function (serverId) {
            if (promise !== undefined) return promise;

            if (!serverId) {
                serverId = $rootScope.server.id;
            }

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                var data = { server_id: serverId };
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
            return ApiLoader.post(url + 'delete', { id: id });
        },
        findUsagesInRouteTables: function (id) {
            return ApiLoader.post(url + 'find-usages-in-route-tables', { id: id });
        }
    };
});


app.factory('Number', function ($q, ApiLoader, $rootScope) {
    var url = '/json/number/';
    var listA = undefined;
    var listB = undefined;
    var listC = undefined;
    var listGT = undefined;
    var promiseA = undefined;
    var promiseB = undefined;
    var promiseC = undefined;
    var promiseGT = undefined;
    return {
        clearList: function (type) {
            switch (type) {
                case '1':
                    listA = undefined;
                    promiseA = undefined;
                    break;
                case '2':
                    listB = undefined;
                    promiseB = undefined;
                    break;
                case '3':
                    listC = undefined;
                    promiseC = undefined;
                    break;
                case '4':
                    listGT = undefined;
                    promiseGT = undefined;
                    break;
                default:
                    break;
            }
        },
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        list: function (type, serverId ) {
            if (!serverId) {
                serverId = $rootScope.server.id;
            }

            if (type == 1) {
                if (promiseA !== undefined) return promiseA;

                var deferred = $q.defer();
                if (listA !== undefined) {
                    deferred.resolve(listA);
                    return deferred.promise;
                } else {
                    var data = { server_id: serverId, type_id: type };
                    ApiLoader.post(url + 'list', data)
                        .then(function (data) {
                            listA = data;
                            promiseA = undefined;
                            deferred.resolve(listA);
                        }, function (data) {
                            promiseA = undefined;
                            deferred.reject(data);
                        });
                    promiseA = deferred.promise;
                }
                return deferred.promise;
            } else if (type == 2) {
                if (promiseB !== undefined) return promiseB;

                var deferred = $q.defer();
                if (listB !== undefined) {
                    deferred.resolve(listB);
                    return deferred.promise;
                } else {
                    var data = { server_id: serverId, type_id: type };
                    ApiLoader.post(url + 'list', data)
                        .then(function (data) {
                            listB = data;
                            promiseB = undefined;
                            deferred.resolve(listB);
                        }, function (data) {
                            promiseB = undefined;
                            deferred.reject(data);
                        });
                    promiseB = deferred.promise;
                }
                return deferred.promise;
            } else if (type == 3) {
                if (promiseC !== undefined) return promiseC;

                var deferred = $q.defer();
                if (listC !== undefined) {
                    deferred.resolve(listC);
                    return deferred.promise;
                } else {
                    var data = { server_id: serverId, type_id: type };
                    ApiLoader.post(url + 'list', data)
                        .then(function (data) {
                            listC = data;
                            promiseC = undefined;
                            deferred.resolve(listC);
                        }, function (data) {
                            promiseC = undefined;
                            deferred.reject(data);
                        });
                    promiseC = deferred.promise;
                }
                return deferred.promise;
            } else if (type == 4) {
                if (promiseGT !== undefined) return promiseGT;

                var deferred = $q.defer();
                if (listGT !== undefined) {
                    deferred.resolve(listGT);
                    return deferred.promise;
                } else {
                    var data = { server_id: serverId, type_id: type };
                    ApiLoader.post(url + 'list', data)
                        .then(function (data) {
                            listGT = data;
                            promiseGT = undefined;
                            deferred.resolve(listGT);
                        }, function (data) {
                            promiseGT = undefined;
                            deferred.reject(data);
                        });
                    promiseGT = deferred.promise;
                }
                return deferred.promise;
            }
        },
        listByType: function (data) {
            return ApiLoader.post(url + 'list-by-type', data);
        },
        save: function (data) {
            listA = listB = undefined;
            return ApiLoader.post(url + 'save', data);
        },
        delete: function (id) {
            listA = listB = undefined;
            return ApiLoader.post(url + 'delete', { id: id });
        },
        findUsagesInRouteTables: function (id) {
            return ApiLoader.post(url + 'find-usages-in-route-tables', { id: id });
        },
        findUsagesInTrunkPriority: function (id) {
            return ApiLoader.post(url + 'find-usages-in-trunk-priority', { id: id });
        },
        findUsagesInTrunkRules: function (id) {
            return ApiLoader.post(url + 'find-usages-in-trunk-rules', { id: id });
        },
        findUsagesInStatRules: function (id) {
            return ApiLoader.post(url + 'find-usages-in-stat-rules', { id: id });
        },
    };
});

app.factory('NumberAll', function ($q, ApiLoader, $rootScope) {
    var url = '/json/number/';
    var list = undefined;
    var promise = undefined;
    return {
        clearList: function (type) {
            list = undefined;
            promise = undefined;
        },
        list: function (type, serverId) {
            if (!serverId) {
                serverId = $rootScope.server.id;
            }

            if (promise !== undefined) return promise;

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                var data = { server_id: serverId };
                ApiLoader.post(url + 'list', data)
                    .then(function (data) {
                        list = data;
                        promise = undefined;
                        deferred.resolve(list);
                    }, function (data) {
                        promise = undefined;
                        deferred.reject(data);
                    });
                promise = deferred.promise;
            }
            return deferred.promise;
        },
    };
});

app.factory('Destination', function ($q, ApiLoader, $rootScope) {
    var url = '/json/destination/';
    var list = undefined;
    var promise = undefined;
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
                var data = { server_id: $rootScope.server.id };
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
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('Settings', function ($q, ApiLoader) {
    var url = '/json/settings/';

    return {
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        save: function (data) {
            return ApiLoader.post(url + 'save', data);
        }
    };
});

app.factory('Server', function ($q, ApiLoader) {
    var url = '/json/server/';

    return {
        list: function (data) {
            return ApiLoader.post(url + 'list', data);
        },
        listByHub: function (data) {
            return ApiLoader.post(url + 'list-by-hub', data);
        },
        listByHubWithContract: function (data) {
            return ApiLoader.post(url + 'list-by-hub-with-contract', data);
        },
        checkSyncProgress: function (data) {
            return ApiLoader.post(url + 'check-sync-progress', data);
        }
    };
});

app.factory('ServerOcs', function ($q, ApiLoader) {
    var url = '/json/server-ocs/';

    return {
        list: function (data) {
            return ApiLoader.post(url + 'list', data);
        }
    };
});

app.factory('FmcTrunk', function ($q, ApiLoader) {
    var url = '/json/fmc-trunk/';

    return {
        list: function (data) {
            return ApiLoader.post(url + 'list', data);
        }
    };
});

app.factory('Pbx', function ($q, ApiLoader) {
    var url = '/json/pbx/';

    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', { 'servers': data });
        }
    };
});

app.factory('InstanceSettings', function ($q, ApiLoader) {
    var url = '/json/instance-settings/';
    return {
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        save: function (data) {
            return ApiLoader.post(url + 'save', data);
        }
    };
});

app.factory('BlacklistSettings', function ($q, ApiLoader) {
    var url = '/json/blacklist-settings/';
    return {
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        add: function (data) {
            return ApiLoader.post(url + 'add', data);
        },
        delete: function (data) {
            return ApiLoader.post(url + 'delete', data);
        },
        check: function (data) {
            return ApiLoader.post(url + 'check', data);
        }
    };
});

app.factory('Airp', function ($q, ApiLoader, $rootScope) {
    var url = '/json/airp/';
    var list = undefined;
    var promise = undefined;
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
                var data = { server_id: $rootScope.server.id };
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
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('Cpc', function ($q, ApiLoader, $rootScope) {
    var url = '/json/cpc/';
    var list = undefined;
    var promise = undefined;
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
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('Cdr', function ($q, ApiLoader) {
    var url = '/json/cdr/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        disconnectCauseList: function () {
            if (promise !== undefined) return promise;

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                var data = {};
                ApiLoader.post(url + 'disconnect-cause-list', data)
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
    };
});

app.factory('Header', function ($q, ApiLoader, $rootScope) {
    var url = '/json/header/';
    var list = undefined;
    var promise = undefined;
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
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('HeaderRule', function ($q, ApiLoader, $rootScope) {
    var url = '/json/header-rule/';
    var list = undefined;
    var promise = undefined;
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
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('Mcc', function ($q, ApiLoader, $rootScope) {
    var url = '/json/mcc/';
    var list = undefined;
    var promise = undefined;
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
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('Mnc', function ($q, ApiLoader, $rootScope) {
    var url = '/json/mnc/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        listByMcc: function (data) {
            return ApiLoader.post(url + 'list-by-mcc', data);
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
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('ReleaseReason', function ($q, ApiLoader, $rootScope) {
    var url = '/json/release-reason/';
    var list = undefined;
    var promise = undefined;
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
                var data = { server_id: $rootScope.server.id };
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
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('RouteTable', function ($q, ApiLoader, $rootScope) {
    var url = '/json/route-table/';
    var list = undefined;
    var promise = undefined;
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
                var data = { server_id: $rootScope.server.id };
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
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('RouteReplace', function ($q, ApiLoader, $rootScope) {
    var url = '/json/route-replace/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        saveMultiple: function (data) {
            list = undefined;
            return ApiLoader.post(url + 'save-multiple', data);
        },
    };
});

app.factory('TestAuth', function ($q, ApiLoader, $rootScope) {
    var url = '/json/test-auth/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        result: function (data) {
            return ApiLoader.post(url + 'result', data);
        },
        list: function () {
            if (promise !== undefined) return promise;

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                var data = { server_id: $rootScope.server.id };
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
            return ApiLoader.post(url + 'delete', { id: id });
        },
        descend: function (data) {
            return ApiLoader.post(url + 'descend', data);
        },
        clearCache: function () {
            return ApiLoader.post(url + 'clear-cache');
        },
        trace: function (data) {
            return ApiLoader.post(url + 'trace', data);
        }
    };
});

app.factory('TestPricelist', function ($q, ApiLoader, $rootScope) {
    var url = '/json/test-pricelist/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        result: function (data) {
            return ApiLoader.post(url + 'result', data);
        },
        numberResult: function (data) {
            return ApiLoader.post(url + 'number-result', data);
        },
        list: function () {
            if (promise !== undefined) return promise;

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                var data = { server_id: $rootScope.server.id };
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
            return ApiLoader.post(url + 'delete', { id: id });
        },
        descend: function (data) {
            return ApiLoader.post(url + 'descend', data);
        },
        clearCache: function () {
            return ApiLoader.post(url + 'clear-cache');
        },
        trace: function (data) {
            return ApiLoader.post(url + 'trace', data);
        }
    };
});

app.factory('TestGroup', function ($q, ApiLoader, $rootScope) {
    var url = '/json/test-group/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        result: function (data) {
            return ApiLoader.post(url + 'result', data);
        },
        list: function () {
            if (promise !== undefined) return promise;

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                var data = { server_id: $rootScope.server.id };
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
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});


app.factory('LegType', function ($q, ApiLoader, $rootScope) {
    var url = '/json/leg-type/';
    var list = undefined;
    var promise = undefined;
    return {
        list: function () {
            if (promise !== undefined) return promise;

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                var data = { server_id: $rootScope.server.id };
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
        }
    };
});

app.factory('TestPricelistGroup', function ($q, ApiLoader, $rootScope) {
    var url = '/json/test-pricelist-group/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        result: function (data) {
            return ApiLoader.post(url + 'result', data);
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
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('TestCall', function ($q, ApiLoader, $rootScope) {
    var url = '/json/test-call/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        result: function (data) {
            return ApiLoader.post(url + 'result', data);
        },
        list: function () {
            if (promise !== undefined) return promise;

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                var data = { server_id: $rootScope.server.id };
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
            return ApiLoader.post(url + 'delete', { id: id });
        },
        descend: function (data) {
            return ApiLoader.post(url + 'descend', data);
        },
        clearCache: function () {
            return ApiLoader.post(url + 'clear-cache');
        }
    };
});

app.factory('ImsiPartner', function ($q, ApiLoader, $rootScope) {
    var url = '/json/imsi-partner/';
    var list = undefined;
    var promise = undefined;
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
                var data = { server_id: $rootScope.server.id };
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
            return ApiLoader.post(url + 'save', data);
        },
        delete: function (id) {
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('Pricelist', function ($q, ApiLoader, $rootScope) {
    var url = '/json/pricelist/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        inherit: function (id, name) {
            return ApiLoader.post(url + 'inherit', { id: id, name: name });
        },
        copy: function (id) {
            return ApiLoader.post(url + 'copy', { id: id });
        },
        copyAndMultiply: function (id, multiplier) {
            return ApiLoader.post(url + 'copy-and-multiply', { id: id, multiplier: multiplier });
        },
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        getInCommerce: function (data) {
            return ApiLoader.post(url + 'get-in-commerce', data);
        },
        getInCommercePackage: function (data) {
            return ApiLoader.post(url + 'get-in-commerce-package', data);
        },
        getInCommercePackageSms: function (data) {
            return ApiLoader.post(url + 'get-in-commerce-package-sms', data);
        },
        getInCommercePackageData: function (data) {
            return ApiLoader.post(url + 'get-in-commerce-package-data', data);
        },

        getWithDependents: function (data) {
            return ApiLoader.post(url + 'get-with-dependents', data);
        },
        getWithDependentsNew: function (data) {
            return ApiLoader.post(url + 'get-with-dependents-new', data);
        },
        toggleActive: function (id) {
            return ApiLoader.post(url + 'toggle-active', { id: id });
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
            return ApiLoader.post(url + 'save', data);
        },
        saveAndUpdate: function (data) {
            return ApiLoader.post(url + 'save-and-update', data);
        },
        delete: function (id) {
            return ApiLoader.post(url + 'delete', { id: id });
        },
        search: function (data) {
            return ApiLoader.post(url + 'search', data);
        },
        oldSearch: function (data) {
            return ApiLoader.post(url + 'old-search', data);
        },
        synchronize: function (data) {
            return ApiLoader.post(url + 'synchronize', data);
        }
    };
});

app.factory('OldPricelist', function ($q, ApiLoader, $rootScope) {
    var url = '/json/old-pricelist/';
    var list = undefined;
    var promise = undefined;
    return {
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
        }
    };
});

app.factory('PricelistGroup', function ($q, ApiLoader, $rootScope) {
    var url = '/json/pricelist-group/';
    var list = undefined;
    var promise = undefined;
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
            return ApiLoader.post(url + 'save', data);
        },
        delete: function (id) {
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('MajorGroup', function ($q, ApiLoader, $rootScope) {
    var url = '/json/major-group/';
    var list = undefined;
    var promise = undefined;
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
            return ApiLoader.post(url + 'save', data);
        },
        delete: function (id) {
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('PricelistLocation', function ($q, ApiLoader, $rootScope) {
    var url = '/json/pricelist-location/';
    var list = undefined;
    var promise = undefined;
    return {
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        save: function (data) {
            return ApiLoader.post(url + 'save', data);
        },
        listByPricelist: function (data) {
            return ApiLoader.post(url + 'list-by-pricelist', data);
        },
        delete: function (id) {
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('PricelistFilterA', function ($q, ApiLoader, $rootScope) {
    var url = '/json/pricelist-filter-a/';
    var list = undefined;
    var promise = undefined;
    return {
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        save: function (data) {
            return ApiLoader.post(url + 'save', data);
        },
        saveAndUpdate: function (data) {
            return ApiLoader.post(url + 'save-and-update', data);
        },
        delete: function (id) {
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('PricelistFilterB', function ($q, ApiLoader, $rootScope) {
    var url = '/json/pricelist-filter-b/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        save: function (data) {
            return ApiLoader.post(url + 'save', data);
        },
        saveAndUpdate: function (data) {
            return ApiLoader.post(url + 'save-and-update', data);
        },
        delete: function (id) {
            return ApiLoader.post(url + 'delete', { id: id });
        }
    };
});

app.factory('PricelistPrefixPrice', function ($q, ApiLoader, $rootScope) {
    var url = '/json/pricelist-prefix-price/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
        readPage: function (data) {
            return ApiLoader.post(url + 'read-page', data);
        },
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        },
        save: function (data) {
            return ApiLoader.post(url + 'save', data);
        },
        delete: function (id) {
            return ApiLoader.post(url + 'delete', { id: id });
        },
        singleHistory: function (filterBId, prefixB) {
            return ApiLoader.post(url + 'single-history', { pricelist_filter_b_id: filterBId, prefix_b: prefixB });
        }
    };
});

app.factory('Major', function ($q, ApiLoader, $rootScope) {
    var url = '/json/major/';
    var list = undefined;
    var promise = undefined;
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
            return ApiLoader.post(url + 'save', data);
        },
        saveAndUpdate: function (data) {
            return ApiLoader.post(url + 'save-and-update', data);
        },
        delete: function (id) {
            return ApiLoader.post(url + 'delete', { id: id });
        },
        move: function (id, direction) {
            return ApiLoader.post(url + 'move', { id: id, direction: direction });
        },
        findUsagesInPricelists: function (id) {
            return ApiLoader.post(url + 'find-usages-in-pricelists', { id: id });
        },
        test: function (data) {
            return ApiLoader.post(url + 'test', data);
        },
    };
});

app.factory('Network', function ($q, ApiLoader, $rootScope) {
    var url = '/json/network/';
    var list = undefined;
    var promise = undefined;
    return {
        list: function () {
            if (promise !== undefined) return promise;

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                var data = { server_id: $rootScope.server.id };
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
        }
    };
});

app.factory('SimImsi', function ($q, ApiLoader, $rootScope) {
    var url = '/json/sim-imsi/';
    return {
        partner: function (data) {
            return ApiLoader.post(url + 'partner', data);
        },
        profile: function (data) {
            return ApiLoader.post(url + 'profile', data);
        }
    };
});

app.factory('List', function (Trunk, TrunkGroup, TestGroup, Prefixlist,
    RouteCase, Outcome, Number, NumberAll, Destination,
    Airp, ReleaseReason, RouteTable, Network,
    Attribute, Server, FmcTrunk, Cpc, Hub,
    PricelistGroup, Mcc, Pricelist, TestPricelistGroup,
    MajorGroup, Header, HeaderRule, Cdr, OldPricelist,
    User, ServerOcs, SimImsi, LegType) {
    return {
        trunk: function () {
            return Trunk.list();
        },
        trunkByServer: function (serverId) {
            return Trunk.listByServer(serverId);
        },
        trunkGroup: function () {
            return TrunkGroup.list();
        },
        trunkGroupForMarketplace: function (serverId) {
            return TrunkGroup.listForMarketplace(serverId);
        },
        trunkRoaming: function (servers) {
            return Trunk.listRoaming(servers)
        },
        testGroup: function () {
            return TestGroup.list();
        },
        testPricelistGroup: function () {
            return TestPricelistGroup.list();
        },
        prefixlist: function () {
            return Prefixlist.list();
        },
        prefixlistByType: function (type) {
            return Prefixlist.listByType({ type_id: type });
        },
        attribute: function () {
            return Attribute.list();
        },
        routeCase: function () {
            return RouteCase.list();
        },
        outcome: function (serverId) {
            return Outcome.list(serverId);
        },
        number: function (type, serverId) {
            return Number.list(type, serverId);
        },
        numberClearList: function (type) {
            Number.clearList(type);
        },
        numberAll: function (serverId) {
            return NumberAll.list(serverId);
        },
        destination: function () {
            return Destination.list();
        },
        airp: function () {
            return Airp.list();
        },
        releaseReason: function () {
            return ReleaseReason.list();
        },
        routeTable: function () {
            return RouteTable.list();
        },
        network: function () {
            return Network.list();
        },
        server: function () {
            return Server.list();
        },
        serverOcs: function () {
            return ServerOcs.list();
        },
        fmcTrunk: function () {
            return FmcTrunk.list();
        },
        cpc: function () {
            return Cpc.list();
        },
        pricelistGroup: function () {
            return PricelistGroup.list();
        },
        majorGroup: function () {
            return MajorGroup.list();
        },
        pricelist: function () {
            return Pricelist.list();
        },
        oldPricelist: function () {
            return OldPricelist.list();
        },
        mcc: function () {
            return Mcc.list();
        },
        header: function () {
            return Header.list();
        },
        headerRule: function () {
            return HeaderRule.list();
        },
        disconnectCause: function () {
            return Cdr.disconnectCauseList();
        },
        user: function () {
            return User.read();
        },
        mvnoPartner: function () {
            return SimImsi.partner();
        },
        legType: function () {
            return LegType.list();
        },
        testResult: function () {
            return [
                { 'id': 'not_executed', 'name': 'Не выполнен' },
                { 'id': 'passed', 'name': 'Успех' },
                { 'id': 'failed', 'name': 'Неудача' }
            ];
        },
        uplinkActiveMode: function () {
            return [
                { 'id': 1, 'name': 'all' },
                { 'id': 2, 'name': 'inc' },
                { 'id': 3, 'name': 'exc' }
            ];
        },
        currency: function () {
            return [
                { 'id': 'RUB', 'name': 'RUB' },
                { 'id': 'EUR', 'name': 'EUR' },
                { 'id': 'HUF', 'name': 'HUF' },
                { 'id': 'USD', 'name': 'USD' }
            ];
        },
        location: function () {
            return [
                { 'id': '1', 'name': 'Домашний регион' },
                { 'id': '2', 'name': 'Гостевой регион' },
                { 'id': '3', 'name': 'Международный регион' },
                { 'id': '4', 'name': 'MVNO' },
                { 'id': '5', 'name': 'Не использовать' },
                { 'id': '6', 'name': 'Входящие в международном регионе' },
            ];
        },
        origAttribute: function () {
            return [
                { 'id': '1', 'name': 'МГ/МН-о' },
                { 'id': '2', 'name': 'МГ/МН2-о' }
            ];
        },
        termAttribute: function () {
            return [
                { 'id': '3', 'name': 'МГ/МН-т' },
                { 'id': '4', 'name': 'МГ/МН2-т' }
            ];
        },
        headerRuleItemMode: function () {
            return [
                { 'id': '1', 'name': 'Равно' },
                { 'id': '2', 'name': 'Не равно' },
                { 'id': '3', 'name': 'Присутствует' },
                { 'id': '4', 'name': 'Отсутствует' },
                { 'id': '5', 'name': 'Regexp' }
            ];
        },
        timeInterval: function () {
            return [
                { 'id': '60', 'name': 'Искать за последнюю минуту' },
                { 'id': '300', 'name': 'Искать за последние 5 минут' },
                { 'id': '900', 'name': 'Искать за последние 15 минут' },
                { 'id': '1800', 'name': 'Искать за последние 30 минут' },
                { 'id': '3600', 'name': 'Искать за последний час' },
                { 'id': '7200', 'name': 'Искать за последние 2 часа' },
                { 'id': '28800', 'name': 'Искать за последние 8 часов' },
                { 'id': '86400', 'name': 'Искать за последний день' },
                { 'id': '172800', 'name': 'Искать за последние 2 дня' },
                { 'id': '432000', 'name': 'Искать за последние 5 дней' },
                { 'id': '604800', 'name': 'Искать за последние 7 дней' },
                { 'id': '1209600', 'name': 'Искать за последние 14 дней' },
                { 'id': '2592000', 'name': 'Искать за последние 30 дней' },
                { 'id': '0', 'name': 'Искать за все время' }
            ];
        },
        considerPortingMode: function () {
            return [
                { 'id': 1, 'name': 'Пропускать все' },
                { 'id': 2, 'name': 'Пропускать только портированные' },
                { 'id': 3, 'name': 'Пропускать только непортированные' }
            ];
        },
        hub: function () {
            return Hub.list();
        }
    };
});

app.factory('Hub', function ($q, ApiLoader, $rootScope) {
    var url = '/json/hub/';
    return {
        list: function (data) {
            return ApiLoader.post(url + 'list', data);
        }
    };
});

app.factory('Comment', function ($q, ApiLoader, $rootScope) {
    var url = '/json/comment/';
    return {
        save: function (data) {
            return ApiLoader.post(url + 'save', data);
        }
    };
});

app.factory('Nnp', function (ApiLoader) {
    var url = '/json/nnp/';
    return {
        destinationList: function () {
            return ApiLoader.post(url + 'destination');
        },
        countryList: function () {
            return ApiLoader.post(url + 'country');
        },
        regionList: function (data) {
            return ApiLoader.post(url + 'region', data);
        },
        cityList: function (data) {
            return ApiLoader.post(url + 'city', data);
        },
        operatorList: function (data) {
            return ApiLoader.post(url + 'operator', data);
        },
        ndcTypeList: function () {
            return ApiLoader.post(url + 'ndc-type');
        },
        sourceList: function () {
            return ApiLoader.post(url + 'source');
        },
        numberSourceList: function () {
            return ApiLoader.post(url + 'number-source');
        },
        numberStatusList: function () {
            return ApiLoader.post(url + 'number-status');
        },
        geoCountryList: function () {
            return ApiLoader.post(url + 'geo-country');
        },
        geoCityList: function (data) {
            return ApiLoader.post(url + 'geo-city', data);
        },
        ndcList: function (data) {
            return ApiLoader.post(url + 'ndc', data);
        }
    };
});

app.factory('StatisticsTree', function ($q, ApiLoader, $rootScope) {
    var url = '/json/statistics-tree/';
    return {
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        }
    };
});

app.factory('MoneyTree', function ($q, ApiLoader, $rootScope) {
    var url = '/json/money-tree/';
    return {
        get: function (data) {
            return ApiLoader.post(url + 'get', data);
        }
    };
});

app.factory('Scripts', function ($q, ApiLoader, $rootScope) {
    var url = '/json/scripts/';
    return {
        generateTests: function () {
            return ApiLoader.post(url + 'generate-tests');
        },
        deleteTests: function () {
            return ApiLoader.post(url + 'delete-tests');
        },
        viewTestsLog: function () {
            return ApiLoader.post(url + 'view-tests-log');
        },
    };
});

app.factory('PricelistPrefixPriceHistory', function ($q, ApiLoader, $rootScope) {
    var url = '/json/billing/pricelist-prefix-price-history/';
    return {
        undoImport: function (data) {
            return ApiLoader.post(url + 'undo-import', data);
        },
    };
});

app.factory('PricelistPrefixPriceHistoryItem', function ($q, ApiLoader, $rootScope) {
    var url = '/json/billing/pricelist-prefix-price-history-item/';
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
    };
});

app.factory('PricelistFilterBHistoryItem', function ($q, ApiLoader, $rootScope) {
    var url = '/json/billing/pricelist-filter-b-history-item/';
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
    };
});

app.factory('PricelistAlphaNumHistoryItem', function ($q, ApiLoader, $rootScope) {
    var url = '/json/billing/pricelist-alpha-num-history-item/';
    return {
        read: function (data) {
            return ApiLoader.post(url + 'read', data);
        },
    };
});

app.factory('PricelistFilterBHistory', function ($q, ApiLoader, $rootScope) {
    var url = '/json/billing/pricelist-filter-b-history/';
    return {
        undo: function (data) {
            return ApiLoader.post(url + 'undo', data);
        },
    };
});

app.filter('belongsToTestGroup', function () {
    return function (items, groupId) {
        if (!items) {
            return [];
        }

        if (!groupId) {
            return items;
        }

        var filtered = [];

        for (var i = 0; i < items.length; i++) {
            var item = items[i];

            if (item.testgroup_id == groupId) {
                filtered.push(item);
            }
        }

        return filtered;
    };
});

app.filter('testHasResult', function () {
    return function (items, result) {
        if (!items) {
            return [];
        }

        if (!result) {
            return items;
        }

        var filtered = [];

        for (var i = 0; i < items.length; i++) {
            var item = items[i];

            if (item.is_autotest == true && item.result == result) {
                filtered.push(item);
            }
        }

        return filtered;
    };
});