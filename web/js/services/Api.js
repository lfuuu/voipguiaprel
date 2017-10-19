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
            return ApiLoader.post(url + 'delete', {id: id});
        }
    };
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
        read: function(data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function(data) {
            return ApiLoader.post(url + 'get', data);
        },
        list: function() {
            if (promise !== undefined) return promise;

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                var data = {server_id: $rootScope.server.id};
                ApiLoader.post(url + 'list', data)
                    .then(function(data){
                        list = data;
                        promise = undefined;
                        deferred.resolve(data);
                    }, function(data){
                        promise = undefined;
                        deferred.reject(data);
                    });
                promise = deferred.promise;
            }
            return deferred.promise;
        },
        save: function(data) {
            list = undefined;
            return ApiLoader.post(url + 'save', data);
        },
        delete: function(id) {
            list = undefined;
            return ApiLoader.post(url + 'delete', {id: id});
        },
        serviceTrunks: function (trunkId) {
            return ApiLoader.post(url + 'get-service-trunks', {'trunk_id': trunkId});
        }
    };
});

app.factory('TrunkGroup', function ($q, ApiLoader, $rootScope) {
    var
        url = '/json/trunk-group/',
        list = undefined,
        promise = undefined;

    return {
        read: function(data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function(data) {
            return ApiLoader.post(url + 'get', data);
        },
        list: function() {
            if (promise !== undefined) return promise;

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                var data = {server_id: $rootScope.server.id};
                ApiLoader.post(url + 'list', data)
                    .then(function(data){
                        list = data;
                        promise = undefined;
                        deferred.resolve(data);
                    }, function(data){
                        promise = undefined;
                        deferred.reject(data);
                    });
                promise = deferred.promise;
            }
            return deferred.promise;
        },
        findIntoRules: function(data) {
            return ApiLoader.post(url + 'get-trunks-with-group-into-rules', data);
        },
        findIntoPriorities: function(data) {
            return ApiLoader.post(url + 'get-trunks-with-group-into-priorities', data);
        },
        save: function(data) {
            list = undefined;
            return ApiLoader.post(url + 'save', data);
        },
        delete: function(id) {
            list = undefined;
            return ApiLoader.post(url + 'delete', {id: id});
        }
    };
});

app.factory('Prefixlist', function ($q, ApiLoader, $rootScope) {
    var url = '/json/prefixlist/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function(data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function(data) {
            return ApiLoader.post(url + 'get', data);
        },
        list: function() {
            if (promise !== undefined) return promise;

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                var data = {server_id: $rootScope.server.id};
                ApiLoader.post(url + 'list', data)
                    .then(function(data){
                        list = data;
                        promise = undefined;
                        deferred.resolve(data);
                    }, function(data){
                        promise = undefined;
                        deferred.reject(data);
                    });
                promise = deferred.promise;
            }
            return deferred.promise;
        },
        save: function(data) {
            list = undefined;
            return ApiLoader.post(url + 'save', data);
        },
        delete: function(id) {
            list = undefined;
            return ApiLoader.post(url + 'delete', {id: id});
        },
        nnpCalculation: function (id) {
            return ApiLoader.post(url + 'nnp-calculation', {id: id});
        }
    };
});

app.factory('Billing', function (ApiLoader) {
	var url = '/json/billing/';
	return {
		countries: function() {
			return ApiLoader.post(url + 'countries');
		},
		regions: function() {
			return ApiLoader.post(url + 'regions');
		},
		cities: function(geo) {
			return ApiLoader.post(url + 'cities', geo);
		},
		operators: function() {
			return ApiLoader.post(url + 'operators');
		},
		networkTypes: function() {
			return ApiLoader.post(url + 'network-types');
		}
	};
});

app.factory('RouteCase', function ($q, ApiLoader, $rootScope) {
	var url = '/json/route-case/';
	var list = undefined;
	var promise = undefined;
	return {
		read: function(data) {
			return ApiLoader.post(url + 'read', data);
		},
		get: function(data) {
			return ApiLoader.post(url + 'get', data);
		},
		list: function() {
			if (promise !== undefined) return promise;

			var deferred = $q.defer();
			if (list !== undefined) {
				deferred.resolve(list);
				return deferred.promise;
			} else {
				var data = {server_id: $rootScope.server.id};
				ApiLoader.post(url + 'list', data)
					.then(function(data){
						list = data;
						promise = undefined;
						deferred.resolve(data);
					}, function(data){
						promise = undefined;
						deferred.reject(data);
					});
				promise = deferred.promise;
			}
			return deferred.promise;
		},
		save: function(data) {
			list = undefined;
			return ApiLoader.post(url + 'save', data);
		},
		delete: function(id) {
			list = undefined;
			return ApiLoader.post(url + 'delete', {id: id});
		}
	};
});


app.factory('Outcome', function ($q, ApiLoader, $rootScope) {
	var url = '/json/outcome/';
	var list = undefined;
	var promise = undefined;
	return {
		read: function(data) {
			return ApiLoader.post(url + 'read', data);
		},
		get: function(data) {
			return ApiLoader.post(url + 'get', data);
		},
		list: function() {
			if (promise !== undefined) return promise;

			var deferred = $q.defer();
			if (list !== undefined) {
				deferred.resolve(list);
				return deferred.promise;
			} else {
				var data = {server_id: $rootScope.server.id};
				ApiLoader.post(url + 'list', data)
					.then(function(data){
						list = data;
						promise = undefined;
						deferred.resolve(data);
					}, function(data){
						promise = undefined;
						deferred.reject(data);
					});
				promise = deferred.promise;
			}
			return deferred.promise;
		},
		save: function(data) {
			list = undefined;
			return ApiLoader.post(url + 'save', data);
		},
		delete: function(id) {
			list = undefined;
			return ApiLoader.post(url + 'delete', {id: id});
		}
	};
});


app.factory('Number', function ($q, ApiLoader, $rootScope) {
	var url = '/json/number/';
	var listA = undefined;
	var listB = undefined;
	var promiseA = undefined;
	var promiseB = undefined;
	return {
		read: function(data) {
			return ApiLoader.post(url + 'read', data);
		},
		get: function(data) {
			return ApiLoader.post(url + 'get', data);
		},
		list: function(type) {
			if (type == 1) {
				if (promiseA !== undefined) return promiseA;

				var deferred = $q.defer();
				if (listA !== undefined) {
					deferred.resolve(listA);
					return deferred.promise;
				} else {
					var data = {server_id: $rootScope.server.id};
					ApiLoader.post(url + 'list', data)
						.then(function(data){
							listA = [];
							for(var i in data) {
								if (data[i].type_id == 1) {
									listA.push(data[i]);
								}
							}
							promiseA = undefined;
							deferred.resolve(listA);
						}, function(data){
							promiseA = undefined;
							deferred.reject(data);
						});
					promiseA = deferred.promise;
				}
				return deferred.promise;
			} else
			if (type == 2) {
				if (promiseB !== undefined) return promiseB;

				var deferred = $q.defer();
				if (listB !== undefined) {
					deferred.resolve(listB);
					return deferred.promise;
				} else {
					var data = {server_id: $rootScope.server.id};
					ApiLoader.post(url + 'list', data)
						.then(function(data){
							listB = [];
							for(var i in data) {
								if (data[i].type_id == 2) {
									listB.push(data[i]);
								}
							}
							promiseB = undefined;
							deferred.resolve(listB);
						}, function(data){
							promiseB = undefined;
							deferred.reject(data);
						});
					promiseB = deferred.promise;
				}
				return deferred.promise;
			}
		},
		save: function(data) {
			listA = listB = undefined;
			return ApiLoader.post(url + 'save', data);
		},
		delete: function(id) {
			listA = listB = undefined;
			return ApiLoader.post(url + 'delete', {id: id});
		}
	};
});

app.factory('Destination', function ($q, ApiLoader, $rootScope) {
    var url = '/json/destination/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function(data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function(data) {
            return ApiLoader.post(url + 'get', data);
        },
        list: function() {
            if (promise !== undefined) return promise;

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                var data = {server_id: $rootScope.server.id};
                ApiLoader.post(url + 'list', data)
                    .then(function(data){
                        list = data;
                        promise = undefined;
                        deferred.resolve(data);
                    }, function(data){
                        promise = undefined;
                        deferred.reject(data);
                    });
                promise = deferred.promise;
            }
            return deferred.promise;
        },
        save: function(data) {
            list = undefined;
            return ApiLoader.post(url + 'save', data);
        },
        delete: function(id) {
            list = undefined;
            return ApiLoader.post(url + 'delete', {id: id});
        }
    };
});

app.factory('Settings', function ($q, ApiLoader) {
	var url = '/json/settings/';

	return {
		get: function(data) {
			return ApiLoader.post(url + 'get', data);
		},
		save: function(data) {
			return ApiLoader.post(url + 'save', data);
		}
	};
});

app.factory('InstanceSettings', function ($q, ApiLoader) {
	var url = '/json/instance-settings/';
	return {
		get: function(data) {
			return ApiLoader.post(url + 'get', data);
		},
		save: function(data) {
			return ApiLoader.post(url + 'save', data);
		}
	};
});

app.factory('Airp', function ($q, ApiLoader, $rootScope) {
	var url = '/json/airp/';
	var list = undefined;
	var promise = undefined;
	return {
		read: function(data) {
			return ApiLoader.post(url + 'read', data);
		},
		get: function(data) {
			return ApiLoader.post(url + 'get', data);
		},
		list: function() {
			if (promise !== undefined) return promise;

			var deferred = $q.defer();
			if (list !== undefined) {
				deferred.resolve(list);
				return deferred.promise;
			} else {
				var data = {server_id: $rootScope.server.id};
				ApiLoader.post(url + 'list', data)
					.then(function(data){
						list = data;
						promise = undefined;
						deferred.resolve(data);
					}, function(data){
						promise = undefined;
						deferred.reject(data);
					});
				promise = deferred.promise;
			}
			return deferred.promise;
		},
		save: function(data) {
			list = undefined;
			return ApiLoader.post(url + 'save', data);
		},
		delete: function(id) {
			list = undefined;
			return ApiLoader.post(url + 'delete', {id: id});
		}
	};
});

app.factory('ReleaseReason', function ($q, ApiLoader, $rootScope) {
	var url = '/json/release-reason/';
	var list = undefined;
	var promise = undefined;
	return {
		read: function(data) {
			return ApiLoader.post(url + 'read', data);
		},
		get: function(data) {
			return ApiLoader.post(url + 'get', data);
		},
		list: function() {
			if (promise !== undefined) return promise;

			var deferred = $q.defer();
			if (list !== undefined) {
				deferred.resolve(list);
				return deferred.promise;
			} else {
				var data = {server_id: $rootScope.server.id};
				ApiLoader.post(url + 'list', data)
					.then(function(data){
						list = data;
						promise = undefined;
						deferred.resolve(data);
					}, function(data){
						promise = undefined;
						deferred.reject(data);
					});
				promise = deferred.promise;
			}
			return deferred.promise;
		},
		save: function(data) {
			list = undefined;
			return ApiLoader.post(url + 'save', data);
		},
		delete: function(id) {
			list = undefined;
			return ApiLoader.post(url + 'delete', {id: id});
		}
	};
});

app.factory('RouteTable', function ($q, ApiLoader, $rootScope) {
	var url = '/json/route-table/';
	var list = undefined;
	var promise = undefined;
	return {
		read: function(data) {
			return ApiLoader.post(url + 'read', data);
		},
		get: function(data) {
			return ApiLoader.post(url + 'get', data);
		},
		list: function() {
			if (promise !== undefined) return promise;

			var deferred = $q.defer();
			if (list !== undefined) {
				deferred.resolve(list);
				return deferred.promise;
			} else {
				var data = {server_id: $rootScope.server.id};
				ApiLoader.post(url + 'list', data)
					.then(function(data){
						list = data;
						promise = undefined;
						deferred.resolve(data);
					}, function(data){
						promise = undefined;
						deferred.reject(data);
					});
				promise = deferred.promise;
			}
			return deferred.promise;
		},
		save: function(data) {
			list = undefined;
			return ApiLoader.post(url + 'save', data);
		},
		delete: function(id) {
			list = undefined;
			return ApiLoader.post(url + 'delete', {id: id});
		}
	};
});

app.factory('TestAuth', function ($q, ApiLoader, $rootScope) {
	var url = '/json/test-auth/';
	var list = undefined;
	var promise = undefined;
	return {
		read: function(data) {
			return ApiLoader.post(url + 'read', data);
		},
		get: function(data) {
			return ApiLoader.post(url + 'get', data);
		},
		result: function(data) {
			return ApiLoader.post(url + 'result', data);
		},
		list: function() {
			if (promise !== undefined) return promise;

			var deferred = $q.defer();
			if (list !== undefined) {
				deferred.resolve(list);
				return deferred.promise;
			} else {
				var data = {server_id: $rootScope.server.id};
				ApiLoader.post(url + 'list', data)
					.then(function(data){
						list = data;
						promise = undefined;
						deferred.resolve(data);
					}, function(data){
						promise = undefined;
						deferred.reject(data);
					});
				promise = deferred.promise;
			}
			return deferred.promise;
		},
		save: function(data) {
			list = undefined;
			return ApiLoader.post(url + 'save', data);
		},
		delete: function(id) {
			list = undefined;
			return ApiLoader.post(url + 'delete', {id: id});
		}
	};
});

app.factory('TestGroup', function ($q, ApiLoader, $rootScope) {
  var url = '/json/test-group/';
  var list = undefined;
  var promise = undefined;
  return {
    read: function(data) {
      return ApiLoader.post(url + 'read', data);
    },
    get: function(data) {
      return ApiLoader.post(url + 'get', data);
    },
    result: function(data) {
      return ApiLoader.post(url + 'result', data);
    },
    list: function() {
      if (promise !== undefined) return promise;

      var deferred = $q.defer();
      if (list !== undefined) {
        deferred.resolve(list);
        return deferred.promise;
      } else {
        var data = {server_id: $rootScope.server.id};
        ApiLoader.post(url + 'list', data)
          .then(function(data){
            list = data;
            promise = undefined;
            deferred.resolve(data);
          }, function(data){
            promise = undefined;
            deferred.reject(data);
          });
        promise = deferred.promise;
      }
      return deferred.promise;
    },
    save: function(data) {
      list = undefined;
      return ApiLoader.post(url + 'save', data);
    },
    delete: function(id) {
      list = undefined;
      return ApiLoader.post(url + 'delete', {id: id});
    }
  };
});

app.factory('TestCall', function ($q, ApiLoader, $rootScope) {
    var url = '/json/test-call/';
    var list = undefined;
    var promise = undefined;
    return {
        read: function(data) {
            return ApiLoader.post(url + 'read', data);
        },
        get: function(data) {
            return ApiLoader.post(url + 'get', data);
        },
        result: function(data) {
            return ApiLoader.post(url + 'result', data);
        },
        list: function() {
            if (promise !== undefined) return promise;

            var deferred = $q.defer();
            if (list !== undefined) {
                deferred.resolve(list);
                return deferred.promise;
            } else {
                var data = {server_id: $rootScope.server.id};
                ApiLoader.post(url + 'list', data)
                    .then(function(data){
                        list = data;
                        promise = undefined;
                        deferred.resolve(data);
                    }, function(data){
                        promise = undefined;
                        deferred.reject(data);
                    });
                promise = deferred.promise;
            }
            return deferred.promise;
        },
        save: function(data) {
            list = undefined;
            return ApiLoader.post(url + 'save', data);
        },
        delete: function(id) {
            list = undefined;
            return ApiLoader.post(url + 'delete', {id: id});
        }
    };
});

app.factory('Network', function ($q, ApiLoader, $rootScope) {
	var url = '/json/network/';
	var list = undefined;
	var promise = undefined;
	return {
		list: function() {
			if (promise !== undefined) return promise;

			var deferred = $q.defer();
			if (list !== undefined) {
				deferred.resolve(list);
				return deferred.promise;
			} else {
				var data = {server_id: $rootScope.server.id};
				ApiLoader.post(url + 'list', data)
					.then(function(data){
						list = data;
						promise = undefined;
						deferred.resolve(data);
					}, function(data){
						promise = undefined;
						deferred.reject(data);
					});
				promise = deferred.promise;
			}
			return deferred.promise;
		}
	};
});


app.factory('List', function (Trunk, TrunkGroup, TestGroup, Prefixlist, RouteCase, Outcome, Number, Destination, Airp, ReleaseReason, RouteTable, Network, Attribute) {
	return {
		trunk: function() {
			return Trunk.list();
		},
		trunkGroup: function() {
			return TrunkGroup.list();
		},
    testGroup: function() {
      return TestGroup.list();
    },
		prefixlist: function() {
			return Prefixlist.list();
		},
        attribute: function () {
            return Attribute.list();
        },
		routeCase: function() {
			return RouteCase.list();
		},
		outcome: function() {
			return Outcome.list();
		},
		number: function(type) {
			return Number.list(type);
		},
        destination: function () {
            return Destination.list();
        },
		airp: function() {
			return Airp.list();
		},
		releaseReason: function() {
			return ReleaseReason.list();
		},
		routeTable: function() {
			return RouteTable.list();
		},
        network: function () {
            return Network.list();
        }
    };
});

app.factory('Nnp', function (ApiLoader) {
    var url = '/json/nnp/';
    return {
        destinationList: function() {
            return ApiLoader.post(url + 'destination');
        },
        countryList: function () {
            return ApiLoader.post(url + 'country');
        },
        regionList: function (countryCode) {
            return ApiLoader.post(url + 'region?countryCode=' + countryCode);
        },
        cityList: function (countryCode, regionId) {
            return ApiLoader.post(url + 'city?countryCode=' + countryCode + '&regionId=' + regionId);
        },
        operatorList: function (countryCode) {
            return ApiLoader.post(url + 'operator?countryCode=' + countryCode);
        },
        ndcTypeList: function () {
            return ApiLoader.post(url + 'ndc-type');
        }
    };
});

app.filter('belongsToTestGroup', function () {
  return function (items, groupId) {
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
