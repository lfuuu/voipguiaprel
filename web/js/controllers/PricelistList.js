var PricelistListCtrl = function ($scope, Pricelist, List, Redirect, $window, $timeout) {

  $scope.sortType = 'name';
  $scope.sortReverse = false;
  $scope.searchQuery = '';
  $scope.hideFilter = false;
  $scope.searchArray = {
    group_id: '',
    query: '',
    service_type_id: '',
    id: '',
    is_active: '',
    is_orig: '',
    currency: '',
  };

  $scope.filterFields = [
    'id', 'name', 'currency_id', 'group_name', 'date_created'
  ];

  $scope.groupId = 'all';

  $scope.currentPage = 1;
  $scope.limit = 15;
  $scope.offset = (($scope.currentPage - 1) * $scope.limit);
  $scope.totalItems = 0;

  // =========================
  // POPUP-ошибки (красная шапка)
  // =========================
  $scope.popupErrors = null;
  $scope.closeErrorsPopup = function () { $scope.popupErrors = null; };
  function showErrorsPopup(xhr, fallbackText) {
    var errors = [];
    var header = 'Ошибка';
    if (xhr && typeof xhr.status !== 'undefined') header += ' ' + xhr.status;

    if (xhr && xhr.data) {
      if (xhr.data.errors && xhr.data.errors.length) {
        for (var i = 0; i < xhr.data.errors.length; i++) {
          var e = xhr.data.errors[i] || {};
          errors.push({ code: e.code || null, message: e.message || fallbackText || 'Ошибка' });
        }
      } else if (xhr.data.message) {
        errors.push({ code: null, message: xhr.data.message });
      } else if (typeof xhr.data === 'string') {
        errors.push({ code: null, message: xhr.data });
      }
    }
    if (errors.length === 0) errors.push({ code: null, message: fallbackText || 'Неизвестная ошибка' });

    $scope.popupErrors = { header: header, errors: errors };
    $timeout(function () { if ($scope.popupErrors) $scope.popupErrors = null; }, 8000);
  }

  // =========================
  // МОДАЛКА СВЯЗЕЙ
  // =========================
  $scope.relationsModal = {
    visible: false,
    loading: false,
    item: null,
    data: null,   // { package_pricelist:[], package_sms:[], package_data:[], a2p_routes:[{a2psms_route_table_id, ord, nnp_pricelist_id}] }
    error: null
  };

  // Ссылка на внешний стат:
  // https://stat.mcn.ru/uu/tariff/edit?id=<ID>
  $scope.buildTariffLink = function(id) {
    return 'https://stat.mcn.ru/uu/tariff/edit?id=' + encodeURIComponent(id);
  };

  $scope.openRelations = function(item) {
    $scope.relationsModal.visible = true;
    $scope.relationsModal.loading = true;
    $scope.relationsModal.item = item;
    $scope.relationsModal.data = null;
    $scope.relationsModal.error = null;

    Pricelist.relations(item.id)
      .then(function (data) {
        $scope.relationsModal.data = {
          package_pricelist: data.package_pricelist || [],
          package_sms:       data.package_sms       || [],
          package_data:      data.package_data      || [],
          a2p_routes:        data.a2p_routes        || [] // массив объектов с полями (см. backend)
        };
      })
      .catch(function (xhr) {
        showErrorsPopup(xhr, 'Не удалось получить связи прайслиста #' + item.id);
        $scope.relationsModal.error = 'Не удалось получить связи прайслиста #' + item.id;
      })
      .finally(function () {
        $scope.relationsModal.loading = false;
      });
  };

  $scope.closeRelations = function() {
    $scope.relationsModal.visible = false;
    $scope.relationsModal.item = null;
    $scope.relationsModal.data = null;
    $scope.relationsModal.error = null;
  };

  // =========================
  // ИНИТ/ЗАГРУЗКА
  // =========================
  $scope.init = function (tab) {
    if (tab) tab.title = 'Pricelist';
    $scope.refreshList();
  };

  $scope.refreshList = function () {
    Pricelist.read({
      search_array: $scope.searchArray,
      offset: $scope.offset,
      limit: $scope.limit
    }).then(function (data) {
      $scope.list = data.data;
      $scope.totalItems = data.totalCount;
    });
  };

  List.pricelistGroup().then(function (data) { $scope.groupList = data; });
  List.currency().then(function (data) { $scope.currency = data; });

  $scope.clickSearch = function () { $scope.refreshList(); };

  $scope.clickCreate = function () {
    Redirect.pricelistCreate($scope.groupId).then(function () { $scope.init(); });
  };

  $scope.clickItem = function (item) {
    if (!userPermissions['pricelist_list']) return;
    if (window.getSelection().type == 'Range') return;

    Redirect.pricelistShortView(item.id, item.service_type_id).then(function () { $scope.init(); });
  };

  // =========================
  // ДЕЙСТВИЯ
  // =========================
  $scope.copyItem = function (item) {
    if (!$window.confirm('Копировать?')) return;
    Pricelist.copy(item.id).then(function (response) {
      $scope.init();
      Redirect.pricelistShortView(response.id, response.service_type_id).then(function () { $scope.init(); });
    }).catch(function (xhr) { showErrorsPopup(xhr, 'Ошибка при копировании прайслиста #' + item.id); });
  };

  $scope.copyAndMultiplyItem = function (item) {
    var multiplier = parseFloat($window.prompt('Введите множитель для копирования'));
    if (isNaN(multiplier)) return;

    Pricelist.copyAndMultiply(item.id, multiplier).then(function (response) {
      $scope.init();
      Redirect.pricelistShortView(response.id, response.service_type_id).then(function () { $scope.init(); });
    }).catch(function (xhr) { showErrorsPopup(xhr, 'Ошибка при копировании с множителем прайслиста #' + item.id); });
  };

  $scope.updatePrefixPricesItem = function (item) {
    var multiplier = parseFloat($window.prompt('Введите множитель для копирования'));
    var dateFrom = (new String($window.prompt('Введите дату начала'))).toString();
    var dateTo = (new String($window.prompt('Введите дату окончания'))).toString();
    if (isNaN(multiplier) || multiplier <= 0 || !dateTo || !dateFrom) return;

    Pricelist.updatePrefixPrices(item.id, multiplier, dateFrom, dateTo).then(function (response) {
      $scope.init();
      Redirect.pricelistShortView(item.id, response.service_type_id).then(function () { $scope.init(); });
    }).catch(function (xhr) { showErrorsPopup(xhr, 'Ошибка при обновлении цен префиксов для прайслиста #' + item.id); });
  };

  // =========================
  // АЛЕРТЫ ЧЕРЕЗ window.alert
  // =========================
  function extractErrorMessage(resp, fallback) {
    function tryParseJson(str) { try { return JSON.parse(str); } catch (_) { return null; } }
    function pickFromData(data) {
      if (!data) return null;
      if (typeof data === 'string') {
        var j = tryParseJson(data);
        if (j) return pickFromData(j) || data;
        return data;
      }
      if (data.errors && data.errors.length) {
        var parts = [];
        for (var i = 0; i < data.errors.length; i++) {
          var e = data.errors[i] || {};
          var code = e.code ? ('[' + e.code + '] ') : '';
          parts.push(code + (e.message || JSON.stringify(e)));
        }
        return parts.join('\n');
      }
      if (data.message) return data.message;
      if (data.error && typeof data.error === 'string') return data.error;
      if (data.text  && typeof data.text  === 'string') return data.text;
      return null;
    }
    var msg = pickFromData(resp && resp.data) || pickFromData(resp);
    if (msg) return msg;

    if (Array.isArray(resp) && resp.length) {
      for (var i = 0; i < resp.length; i++) {
        var r = resp[i];
        msg = pickFromData(r && r.data) || pickFromData(r);
        if (msg) return msg;
      }
    }
    if (resp && typeof resp.responseText === 'string') {
      var j = tryParseJson(resp.responseText);
      msg = pickFromData(j) || resp.responseText;
      if (msg) return msg;
    }
    if (resp && typeof resp.statusText === 'string' && resp.statusText) return resp.statusText;
    if (typeof resp === 'string') {
      var jj = tryParseJson(resp);
      return pickFromData(jj) || resp;
    }
    return fallback || 'Неизвестная ошибка';
  }

  function showErrorAlert(resp, fallback) {
    var msg = extractErrorMessage(resp, fallback);
    try { msg = String(msg).replace(/\s+$/, ''); } catch (_) {}
    window.alert(msg);
  }

  $scope.deleteItem = function (item) {
    if (!$window.confirm('Удалить прайслист #' + item.id + ' ?')) return;
    Pricelist.delete(item.id).then(
      function success() { $scope.init(); },
      function error(resp) { showErrorAlert(resp, 'Ошибка при удалении прайслиста #' + item.id); }
    ).catch(function (resp) {
      showErrorAlert(resp, 'Ошибка при удалении прайслиста #' + item.id);
    });
  };

  $scope.toggleActive = function (item) {
    var text = item.is_active ? 'Деактивировать?' : 'Активировать?';
    if (!$window.confirm(text)) return;

    Pricelist.toggleActive(item.id).then(function () {
      $scope.init();
    }).catch(function (xhr) {
      showErrorsPopup(xhr, 'Ошибка при смене активности прайслиста #' + item.id);
    });
  };

  $scope.setPagingData = function (page) {
    $scope.offset = ((page - 1) * $scope.limit);
    $scope.refreshList();
  };

  $scope.synchronize = function () {
    Pricelist.synchronize().then(function () {
    }).catch(function (xhr) {
      showErrorsPopup(xhr, 'Ошибка при синхронизации прайслистов');
    });
  };

  $scope.checkCommerce = function (item) {
    if (item.type_id == 2 && item.service_type_id == 1) {
      Redirect.pricelistInCommerceView(item.id).then(function () { $scope.init(); });
    } else {
      Redirect.pricelistInCommerceViewPackage(item).then(function () { $scope.init(); });
    }
  };

  Pricelist.isTriggerEnabled().then(function (flag) { $scope.isTrigger = flag; });

  $scope.switchTriggerOn = function () {
    Pricelist.switchTriggerOn().then(function () { $scope.init(); })
      .catch(function (xhr) { showErrorsPopup(xhr, 'Ошибка при включении триггеров'); });
  };

  $scope.switchTriggerOff = function () {
    Pricelist.switchTriggerOff().then(function () { $scope.init(); })
      .catch(function (xhr) { showErrorsPopup(xhr, 'Ошибка при выключении триггеров'); });
  };

  $scope.notifyEventToAll = function () {
    Pricelist.notifyEventToAll().then(function () { $window.alert('Синхронизация завершена'); })
      .catch(function (xhr) { showErrorsPopup(xhr, 'Ошибка при синхронизации событий'); });
  };
};
