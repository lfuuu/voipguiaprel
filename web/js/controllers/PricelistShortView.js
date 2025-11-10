var PricelistShortViewCtrl = function ($scope, Redirect, List, Pricelist, PricelistLocation, PricelistFilterA, PricelistFilterB, PricelistPrefixPrice, params, $modalInstance, $window, $timeout) {

  $scope.locationIds = List.location();

  $scope.limit = 10;
  $scope.prefixes = [];
  $scope.item = {};
  $scope.pricelistServiceTypeId = 0; // дефолт
  $scope.elementType = params && params.element_type || null;
  $scope.elementId   = params && params.element_id   || null;

  var date = new Date();
  $scope.dateNow = date.toISOString().slice(0, 10);

  $scope.drawTable = function (data) {
    $scope.list = data || [];

    // На всякий — если по каким-то причинам initData не заполнил тип услуги,
    // пробуем вытащить его из массива таблицы.
    if (!$scope.pricelistServiceTypeId) {
      var pl = Array.isArray($scope.list) ? $scope.list.find(function (r) { return r && r.is_pricelist; }) : null;
      if (pl) {
        $scope.pricelistServiceTypeId = Number(pl.service_type_id) || 0;
      }
    }

    // отскроллить к помеченному элементу
    $timeout($scope.focusOnMark, 0);
  };

  $scope.simplifyPrefixList = function (data) {
    var simplifiedPrefixList = {};
    var prefixCount = 0;

    data.sort(function(a, b) {
      if ((a.prefix_b + ' ') > (b.prefix_b + ' ')) return 1;
      if ((a.prefix_b + ' ') < (b.prefix_b + ' ')) return -1;
      // при одинаковом префиксе — по дате
      return a.date_from > b.date_from ? 1 : -1;
    });

    for (var prefixPriceKey in data) {
      var prefixItem = data[prefixPriceKey];
      var bNumberPrice = (parseFloat(prefixItem.b_number_price)).toFixed(6);
      var hasPrefixMark = ($scope.elementType === 'prefix' && String($scope.elementId) === String(prefixItem.id));

      var key = prefixItem.prefix_b + ' ';
      if (simplifiedPrefixList[key] && prefixItem.date_from >= $scope.dateNow) {
        var previousItem = simplifiedPrefixList[key][ simplifiedPrefixList[key].length - 1 ];
        var previousPrice = parseFloat(previousItem.b_number_price);

        simplifiedPrefixList[key].push({
          prefix_price_id: prefixItem.id,
          has_prefix_mark: hasPrefixMark,
          b_number_price: bNumberPrice,
          date_from: prefixItem.date_from,
          date_to: prefixItem.date_to,
          price_change: previousPrice > bNumberPrice ? 'decrease' : (previousPrice == bNumberPrice ? 'none' : 'increase')
        });
      } else {
        if (!simplifiedPrefixList[key]) {
          prefixCount++;
        }
        simplifiedPrefixList[key] = [{
          prefix_price_id: prefixItem.id,
          has_prefix_mark: hasPrefixMark,
          b_number_price: bNumberPrice,
          date_from: prefixItem.date_from,
          date_to: prefixItem.date_to,
          price_change: 'none'
        }];
      }
    }
    return { list: simplifiedPrefixList, count: prefixCount };
  };

  $scope.focusOnMark = function () {
    var el = $('[data-has-mark="true"]')[0];
    if (el) el.scrollIntoView(true);
  };

  $scope.initData = function (id, markType, markId) {
    Pricelist.getWithDependentsNew({ id: id, mark_type: markType, mark_id: markId, type: 'short' }).then(function (data) {
      // Ищем строку прайслиста безопасно
      var pl = Array.isArray(data) ? data.find(function (r) { return r && r.is_pricelist; }) : null;
      // Фолбэк: если API вернул «краткую» форму, где 0-й элемент — прайслист
      if (!pl && Array.isArray(data) && data[0]) pl = data[0];

      $scope.item = $scope.item || {};
      if (pl) {
        $scope.item.id         = pl.id;
        $scope.item.date_start = pl.date_start;
        $scope.pricelistIsActive      = pl.is_active;
        $scope.pricelistDateStart     = pl.date_start;
        $scope.pricelistServiceTypeId = Number(pl.service_type_id) || 0;
        $scope.pricelistName          = pl.name;
      }

      $scope.drawTable(data);
    });
  };

  if (params && params.id) {
    if (params.element_type && params.element_id) {
      $scope.initData(params.id, params.element_type, params.element_id);
    }
    $scope.initData(params.id, null, null);
  }

  $scope.back = function () {
    $modalInstance.dismiss();
  };

  $scope.setPagingData = function (page, filterBId) {
    $scope.getPrefixPriceList(filterBId, page);
  };

  $scope.getPrefixPriceList = function (filter_b_id, page) {
    PricelistPrefixPrice.readPage({ pricelist_filter_b_id: filter_b_id, page_number: page }).then(function (data) {
      $scope.prefixes[filter_b_id] = data;

      var simplifiedPrefixResult = $scope.simplifyPrefixList(data);
      var simplifiedPrefixList = simplifiedPrefixResult.list;
      var prefixCount = simplifiedPrefixResult.count + 1;

      var index = '';
      var headerItem;

      // ищем первую строку-контейнер для префиксов этого Filter B
      for (var i in $scope.list) {
        if ($scope.list[i].is_prefix_price === true && $scope.list[i].filter_b_id == filter_b_id) {
          if (index === '') {
            headerItem = $scope.list[i];
            index = i;
            break;
          }
        }
      }

      // подправим счётчик у заголовка Filter A (если есть)
      if (headerItem) {
        for (var j in $scope.list) {
          if ($scope.list[j].is_filter_a_header === true && $scope.list[j].filter_a_id == headerItem.filter_a_id && index != j) {
            $scope.list[j].total_prefix_count = parseInt($scope.list[j].total_prefix_count);
          }
        }

        // удаляем старый «пакет» строк префиксов
        $scope.list.splice(index, headerItem.prefix_count);

        var hasFilterBHeader = false;
        var hasFilterAHeader = headerItem.is_filter_a_header;
        var indexCounter = 0;

        for (var prefixB in simplifiedPrefixList) {
          var item = simplifiedPrefixList[prefixB];
          var toPush;

          if (!hasFilterBHeader) {
            toPush = {
              is_filter_b_header: true,
              is_filter_a_header: headerItem.is_filter_a_header,
              has_filter_a_mark: headerItem.has_filter_a_mark,
              has_filter_b_mark: headerItem.has_filter_b_mark,
              filter_a_name: headerItem.filter_a_name,
              filter_b_name: headerItem.filter_b_name,
              filter_b_rating: headerItem.filter_b_rating,
              filter_b_use_for_minimum: headerItem.filter_b_use_for_minimum,
              filter_a_id: headerItem.filter_a_id,
              filter_b_id: headerItem.filter_b_id,
              is_prefix_price: true,
              prefix_b: prefixB == 'null' ? '' : prefixB,
              prefix_count: prefixCount,
              total_prefix_count: parseInt(headerItem.total_prefix_count),
              total_pagination_count: headerItem.total_pagination_count,
              interconnect_price: headerItem.interconnect_price,
              prefixes: item
            };
            $scope.list.splice(parseInt(index) + parseInt(indexCounter), 0, toPush);
            hasFilterBHeader = true;
          } else {
            toPush = {
              is_filter_b_header: false,
              filter_b_id: headerItem.filter_b_id,
              is_filter_a_header: !hasFilterAHeader,
              is_prefix_price: true,
              prefix_b: prefixB == 'null' ? '' : prefixB,
              prefixes: item
            };
            $scope.list.splice(parseInt(index) + parseInt(indexCounter), 0, toPush);
          }

          indexCounter++;
          hasFilterAHeader = true;
        }

        // футер-пагинация
        $scope.list.splice(parseInt(index) + parseInt(indexCounter), 0, {
          is_filter_b_header: false,
          is_filter_a_header: false,
          is_prefix_price: false,
          is_prefix_price_footer: true,
          totalCount: headerItem.total_pagination_count,
          currentPage: page,
          offset: 0,
          filter_b_id: headerItem.filter_b_id
        });
      }
    });
  };

  $scope.viewPricelist = function (id) {
    Redirect.pricelistView(id).then(function () {
      $scope.initData($scope.item.id);
    }, function () {
      $scope.initData($scope.item.id);
    });
  };

  $scope.editLocation = function (id) {
    // добавлен 3-й аргумент — прокидываем тип услуги
    Redirect.pricelistLocationEdit(id, $scope.pricelistIsActive, $scope.pricelistServiceTypeId).then(function () {
      $scope.initData($scope.item.id);
    }, function () {
      $scope.initData($scope.item.id);
    });
  };

  $scope.editFilterA = function (id) {
    Redirect.pricelistFilterAEdit(id, $scope.pricelistIsActive).then(function () {
      $scope.initData($scope.item.id);
    }, function () {
      $scope.initData($scope.item.id);
    });
  };

  $scope.editFilterB = function (id) {
    Redirect.pricelistFilterBEdit(id, $scope.pricelistIsActive, $scope.item.date_start).then(function () {
      $scope.initData($scope.item.id);
    }, function () {
      $scope.initData($scope.item.id);
    });
  };

  $scope.editPrefixPrice = function (id) {
    Redirect.pricelistPrefixPriceEdit(id, $scope.pricelistIsActive, $scope.item.id).then(function () {
      $scope.initData($scope.item.id);
    }, function () {
      $scope.initData($scope.item.id);
    });
  };

  $scope.viewPrefixHistory = function (prefixB, pricelistFilterBId) {
    Redirect.pricelistSinglePrefixHistoryView(pricelistFilterBId, prefixB).then(function () {
      $scope.initData($scope.item.id);
    });
  };

  $scope.searchResults = {};

  $scope.searchPrefixById = function(searchValue, filterBId) {
    if (!searchValue) {
      alert('Пожалуйста, введите значение prefix_b для поиска.');
      return;
    }
    var requestData = { pricelist_filter_b_id: filterBId };
    PricelistPrefixPrice.readAll(requestData).then(function(data) {
      // Удаление дубликатов по prefix_b
      const uniqueData = Array.from(data.reduce((map, item) => map.set(item.prefix_b, item), new Map()).values());
      // Сортировка по prefix_b
      uniqueData.sort(function(a, b) {
        if (a.prefix_b < b.prefix_b) return -1;
        if (a.prefix_b > b.prefix_b) return 1;
        return 0;
      });
      var index = uniqueData.findIndex(function(item) {
        return item.prefix_b && item.prefix_b.trim() === searchValue.trim();
      });
      if (index !== -1) {
        var pageNumber = Math.floor(index / $scope.limit) + 1;
        // Обновляем currentPage для соответствующих записей
        $scope.list.forEach(function(item) {
          if (item.filter_b_id === filterBId) {
            item.currentPage = pageNumber;
          }
        });
        $scope.setPagingData(pageNumber, filterBId);
        if (!$scope.$$phase) $scope.$apply();
      } else {
        alert('Префикс с таким значением prefix_b не найден.');
      }
    }).catch(function(error) {
      console.error("Произошла ошибка при поиске:", error);
    });
  };

  $scope.printToExcelExpanded = function () {
    window.open('/pricelist/excel?id=' + $scope.item.id, '_blank');
  };

  $scope.printToExcel = function () {
    window.open('/pricelist/excel?id=' + $scope.item.id,'_blank');
  };

  $scope.printToExcelSingleLine = function () {
    window.open('/pricelist/excel-single-line?id=' + $scope.item.id,'_blank');
  };

  $scope.printToExcelPrefixesNew = function () {
    Redirect.pricelistExcelPrefixesParamsEdit($scope.item.id);
  };

  $scope.displayEmptyAlert = function () {
    alert('Это пустая строка.');
  };
};
