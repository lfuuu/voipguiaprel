app.filter('basicFilter', function () {
  return function (items, searchQuery, filterFields) {
    if (!searchQuery) {
      return items;
    }

    searchQuery = searchQuery.toLowerCase();

    return items.filter(function (element) {
      for (var i in filterFields) {
        if (typeof element[filterFields[i]] !== 'undefined' && element[filterFields[i]] !== null && String(element[filterFields[i]]).toLowerCase().indexOf(searchQuery) !== -1) {
          return true;
        }
      }
    });
  };
});

app.filter('trunkFilter', function () {
  return function (items, searchQuery, filterFields) {
    if (!searchQuery) {
      return items;
    }

    searchQuery = searchQuery.toLowerCase();

    return items.filter(function (element) {
      for (var i in filterFields) {
        if (typeof element[filterFields[i]] !== 'undefined' && element[filterFields[i]] !== null && String(element[filterFields[i]]).toLowerCase().indexOf(searchQuery) !== -1) {
          return true;
        }
      }

      if (element['routeTable'] != null && typeof element['routeTable']['name'] !== 'undefined' && element['routeTable']['name'] !== null && String(element['routeTable']['name']).toLowerCase().indexOf(searchQuery) !== -1) {
        return true;
      }
    });
  };
});

app.filter('outcomeFilter', function () {
  return function (items, searchQuery, filterFields) {
    if (!searchQuery) {
      return items;
    }

    var types = {
      1: 'Автоматически',
      2: 'Route case',
      3: 'Release reason',
      4: 'AIRP',
      5: 'Accept',
      6: 'Meg-to-Reg',
      7: 'Meg-to-Meg',
      9: 'Автоматически 2'
    };

    searchQuery = searchQuery.toLowerCase();

    return items.filter(function (element) {
      for (var i in filterFields) {
        if (typeof element[filterFields[i]] !== 'undefined' && element[filterFields[i]] !== null && String(element[filterFields[i]]).toLowerCase().indexOf(searchQuery) !== -1) {
          return true;
        }
      }

      if (typeof types[element['type_id']] !== 'undefined' && types[element['type_id']] !== null && String(types[element['type_id']]).toLowerCase().indexOf(searchQuery) !== -1) {
        return true;
      }

      if (element['routeCase'] != null && typeof element['routeCase']['name'] !== 'undefined' && element['routeCase']['name'] !== null && String(element['routeCase']['name']).toLowerCase().indexOf(searchQuery) !== -1) {
        return true;
      }

      if (element['releaseReason'] != null && typeof element['releaseReason']['name'] !== 'undefined' && element['releaseReason']['name'] !== null && String(element['releaseReason']['name']).toLowerCase().indexOf(searchQuery) !== -1) {
        return true;
      }

      if (element['airp'] != null && typeof element['airp']['name'] !== 'undefined' && element['airp']['name'] !== null && String(element['airp']['name']).toLowerCase().indexOf(searchQuery) !== -1) {
        return true;
      }
    });
  };
});

app.filter('routeCaseFilter', function () {
  return function (items, searchQuery, filterFields) {
    if (!searchQuery) {
      return items;
    }

    searchQuery = searchQuery.toLowerCase();

    return items.filter(function (element) {
      for (var i in filterFields) {
        if (typeof element[filterFields[i]] !== 'undefined' && element[filterFields[i]] !== null && String(element[filterFields[i]]).toLowerCase().indexOf(searchQuery) !== -1) {
          return true;
        }
      }

      for (var i in element.trunks) {
        if (typeof element.trunks[i]['priority'] !== 'undefined' && element.trunks[i]['priority'] !== null && String(element.trunks[i]['priority']).toLowerCase().indexOf(searchQuery) !== -1) {
          return true;
        }

        if (typeof element.trunks[i]['weight'] !== 'undefined' && element.trunks[i]['weight'] !== null && String(element.trunks[i]['weight']).toLowerCase().indexOf(searchQuery) !== -1) {
          return true;
        }

        if (typeof element.trunks[i]['trunk']['name'] !== 'undefined' && element.trunks[i]['trunk']['name'] !== null && String(element.trunks[i]['trunk']['name']).toLowerCase().indexOf(searchQuery) !== -1) {
          return true;
        }
      }
    });
  };
});

app.filter('prefixlistFilter', function () {
  return function (items, searchQuery, filterFields) {
    if (!searchQuery) {
      return items;
    }

    var types = {
      1: 'Вручную',
      2: 'Местные префиксы',
      3: 'РосСвязь',
      4: 'CSV',
      5: 'Дорогие коды',
      6: 'NNP',
      7: '7800',
      8: 'Did на ВАТС',
      9: 'FMC',
      10: 'Parted num',
      11: 'Роуминг'
    };

    searchQuery = searchQuery.toLowerCase();

    return items.filter(function (element) {
      for (var i in filterFields) {
        if (typeof element[filterFields[i]] !== 'undefined' && element[filterFields[i]] !== null && String(element[filterFields[i]]).toLowerCase().indexOf(searchQuery) !== -1) {
          return true;
        }
      }

      if (typeof types[element['type_id']] !== 'undefined' && types[element['type_id']] !== null && String(types[element['type_id']]).toLowerCase().indexOf(searchQuery) !== -1) {
        return true;
      }
    });
  };
});