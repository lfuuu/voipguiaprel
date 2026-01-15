/* js/controllers/PricelistLocationEdit.js */
var PricelistLocationEditCtrl = function($scope, List, SimImsi, PricelistLocation, Mcc, Mnc, params, $modal, $modalInstance, $window, Redirect) {

  // --- UI Tabs ---
  $scope.ui = { activeTab: 'single' }; // 'single' | 'bulk'

  $scope.params = params; // для шаблона
  $scope.pricelistIsActive = params.pricelist_is_active;
  $scope.pricelistServiceTypeId = params.pricelist_service_type_id;

  var watchers = {
    mcc: function (newValue, oldValue) {
      if (newValue != oldValue) {
        Mnc.listByMcc({mcc: newValue}).then(function (data) {
          $scope.mncList = data;
        });
      }
    }
  };

  if (params.id) {
    PricelistLocation.get({id: params.id}).then(function(data){
      $scope.item = data;

      if ($scope.item.mcc)         { $scope.item.mcc = $scope.item.mcc.replace('{', '').replace('}', '').split(','); }
      if ($scope.item.sim_partner) { $scope.item.sim_partner = $scope.item.sim_partner.replace('{', '').replace('}', '').split(','); }
      if ($scope.item.sim_profile) { $scope.item.sim_profile = $scope.item.sim_profile.replace('{', '').replace('}', '').split(','); }

      Mnc.listByMcc({mcc: $scope.item.mcc}).then(function (data) {
        $scope.mncList = data;
        $scope.item.mnc = $scope.item.mnc.replace('{', '').replace('}', '').split(',');
      });

      $scope.$watch('item.mcc', watchers.mcc);
    });
  } else if (params.pricelist_id) {
    $scope.item = { pricelist_id: params.pricelist_id, location_id: 1 };
    $scope.$watch('item.mcc', watchers.mcc);
  } else {
    $scope.item = { location_id: 1 };
    $scope.$watch('item.mcc', watchers.mcc);
  }

  Mcc.list().then(function (result) { $scope.mccList = result; });
  SimImsi.partner().then(function (result) { $scope.partnerList = result; });
  SimImsi.profile().then(function (result) { $scope.profileList = result; });
  $scope.location = List.location();

  // --- Helpers ---
  $scope.getPricelistId = function(){
    return ($scope.item && $scope.item.pricelist_id) || ($scope.params && $scope.params.pricelist_id) || null;
  };
  function toIntArray(arr){
    var out=[]; arr=arr||[];
    for (var i=0;i<arr.length;i++){ var n=parseInt(arr[i],10); if(!isNaN(n)) out.push(n); }
    return out;
  }
  function trim(s){ return (s || '').replace(/^\s+|\s+$/g, ''); }
  function detectDelimiter(line) {
    var c = [';', '\t', ',', '|'], best = ';', bestCols = 0;
    for (var i=0;i<c.length;i++){ var d=c[i], n=(line||'').split(d).length; if(n>bestCols){bestCols=n;best=d;} }
    return best;
  }
  var intRe = /^\d+$/;
  var priceRe = /^-?\d{1,4}(\.\d{1,6})?$/; // numeric(10,6)

  // --- SINGLE SAVE ---
  $scope.save = function(){
    var data = angular.copy($scope.item);
    data.mcc         = (Array.isArray(data.mcc)) ? ('{' + data.mcc.join(',') + '}') : '{}';
    data.mnc         = (Array.isArray(data.mnc)) ? ('{' + data.mnc.join(',') + '}') : '{}';
    data.sim_partner = (Array.isArray(data.sim_partner)) ? ('{' + data.sim_partner.join(',') + '}') : '{}';
    data.sim_profile = (Array.isArray(data.sim_profile)) ? ('{' + data.sim_profile.join(',') + '}') : '{}';

    PricelistLocation.save(data).then(function(response) {
      $modalInstance.close();
    });
  };

  $scope.back = function(){ $modalInstance.dismiss(); };

  // === BULK IMPORT (интегрировано) ===
  $scope.bulk = {
    location_id: 1,
    sim_partner: [],
    sim_profile: [],
    rounding_threshold: 0,
    raw: '',
    preview: { rows: [], errors: [] },
    error: null,
    replace: false,
    loadingFile: false,
    xlsxFileName: ''
  };

  $scope.triggerXlsxImport = function () {
    var input = document.getElementById('pl-bulk-xlsx-file');
    if (!input) return;

    if (!input._plXlsxBound) {
      input.addEventListener('change', function (evt) {
        $scope.onXlsxFileChange(evt);
      });
      input._plXlsxBound = true;
    }

    input.click();
  };

  $scope.onXlsxFileChange = function (evt) {
    var target = evt && evt.target;
    var files = target && target.files;
    if (!files || !files.length) return;

    var file = files[0];
    if (target) target.value = '';

    $scope.$applyAsync(function () {
      $scope.bulk.loadingFile = true;
      $scope.bulk.error = null;
      $scope.bulk.preview = { rows: [], errors: [] };
    });

    var reader = new FileReader();
    reader.onload = function (e) {
      var base64 = e.target.result;

      PricelistLocation.parseXlsx(base64).then(function (res) {
        var data = (res && res.data) ? res.data : res;
        $scope.bulk.preview.rows = data.rows || [];
        $scope.bulk.preview.errors = [];

        var issues = data.issues || [];
        for (var i = 0; i < issues.length; i++) {
          var issue = issues[i] || {};
          $scope.bulk.preview.errors.push({
            row: issue.row || '-',
            message: issue.message || issue
          });
        }

        if (!$scope.bulk.raw) {
          // заполним сырой текст для наглядности
          var lines = [];
          $scope.bulk.preview.rows.forEach(function (r) {
            lines.push([r.mcc, r.mnc, r.delta_price, r.description || ''].join(';'));
          });
          $scope.bulk.raw = lines.join('\n');
        }
      }, function (err) {
        var msg =
          (err && err.data && (err.data.message || err.data.error)) ||
          (err && err.statusText) ||
          'Не удалось разобрать XLSX';
        $scope.bulk.error = String(msg);
      }).finally(function () {
        $scope.bulk.loadingFile = false;
      });
    };
    reader.onerror = function () {
      $scope.$applyAsync(function () {
        $scope.bulk.loadingFile = false;
        $scope.bulk.error = 'Ошибка чтения файла';
      });
    };
    reader.readAsDataURL(file);
  };

  // === ЗАМЕНИ ЭТУ ФУНКЦИЮ В PricelistLocationEditCtrl ===
$scope.bulkParse = function () {
  var bulk = $scope.bulk;
  bulk.error = null;
  bulk.preview = { rows: [], errors: [] };

  var raw = (bulk.raw || '');
  if (!raw.trim()) return;

  // Нормализация: NBSP -> пробел, убираем BOM
  raw = raw.replace(/[\u00A0\u2007\u202F]/g, ' ').replace(/\uFEFF/g, '');

  var lines = raw.split(/\r?\n/).filter(function (l) { return trim(l) !== ''; });
  if (!lines.length) lines = [raw];

  function splitLine(line) {
    var delimiter = detectDelimiter(line);
    var parts = (delimiter && line.indexOf(delimiter) !== -1) ? line.split(delimiter) : [line];

    // если явного разделителя нет, пробуем классический пробельный разбор
    if (parts.length === 1 && /\s+/.test(line)) {
      parts = line.trim().split(/\s+/);
    }

    return parts.map(function (p) { return trim(p); });
  }

  function pushRow(mccTok, mncTok, priceTok, rowNum, descrTok) {
    var bad = [];
    var mccStr = trim(mccTok);
    var mncRaw = (mncTok === undefined || mncTok === null) ? '' : trim(mncTok);
    var mncStr = (mncRaw === '') ? '0' : mncRaw; // пустой MNC трактуем как 0 (страна)

    // цена может быть с запятой — конвертируем в точку
    var p = (priceTok || '').replace(',', '.');
    if (!intRe.test(mccStr)) bad.push('MCC');
    if (!intRe.test(mncStr)) bad.push('MNC');
    if (!priceRe.test(p)) bad.push('Цена');

    if (bad.length) {
      bulk.preview.errors.push({ row: rowNum, message: 'Неверные поля: ' + bad.join(', ') });
    } else {
      bulk.preview.rows.push({
        mcc: parseInt(mccStr, 10),
        mnc: parseInt(mncStr, 10),
        delta_price: p,
        description: trim(descrTok || '')
      });
    }
  }

  for (var li = 0; li < lines.length; li++) {
    var line = trim(lines[li]);
    if (!line) continue;

    // Пропускаем возможную строку‑заголовок
    var lower = line.toLowerCase();
    if (/\bmcc\b/.test(lower) && /\bmnc\b/.test(lower) && /(delta_price|price|цена)/.test(lower)) {
      continue;
    }

    var parts = splitLine(line);

    // Если в строке «лента» из нескольких троек подряд (mcc mnc price mcc mnc price ...)
    // и НЕТ описаний, разрежем её на куски по 3 токена.
    if (parts.length >= 6 && parts.length % 3 === 0) {
      for (var i = 0; i < parts.length; i += 3) {
        pushRow(parts[i], parts[i + 1], parts[i + 2], (li + 1) + (i / 3));
      }
      continue;
    }

    // Обычный случай: 3..N токенов, где после первых трёх — описание
    var mcc = parts[0];
    var mnc = parts.length > 1 ? parts[1] : '';
    var price = parts.length > 2 ? parts[2] : '';
    var descr = parts.length > 3 ? parts.slice(3).join(' ') : '';
    pushRow(mcc, mnc, price, li + 1, descr);
  }
};


  $scope.bulkImport = function(){
  var bulk = $scope.bulk;
  bulk.error = null;

  if (!bulk.preview.rows.length && bulk.raw) { $scope.bulkParse(); }
  if (!bulk.preview.rows.length || bulk.preview.errors.length) return;

  var payload = {
    pricelist_id: $scope.getPricelistId(),
    location_id: bulk.location_id,
    sim_partner: toIntArray(bulk.sim_partner),
    sim_profile: toIntArray(bulk.sim_profile),
    rounding_threshold: ($scope.pricelistServiceTypeId == 3) ? parseInt(bulk.rounding_threshold || 0, 10) : 0,
    rows: bulk.preview.rows
  };

  PricelistLocation.bulkImport(payload).then(function(res){
    // <-- УНИВЕРСАЛЬНО РАЗБИРАЕМ
    var data = (res && typeof res === 'object' && 'data' in res) ? res.data : res;

    // Если сервер вернул валидную сводку — это успех
    if (data && typeof data.inserted !== 'undefined' && typeof data.failed !== 'undefined') {
      // Можно показать тост, если он у вас есть
      // $rootScope.$emit('toast', {type:'success', text:'Импортировано: '+data.inserted});

      // Закрываем модалку и отдаём сводку родителю
      $modalInstance.close(data);
      return;
    }

    // Иначе считаем это логической ошибкой
    bulk.error = 'Некорректный ответ сервера';
  }, function(err){
    // <-- Ошибочная ветка: выводим ТОЛЬКО строку
    var msg =
      (err && err.data && (err.data.message || err.data.error)) ||
      (err && err.statusText) ||
      'Ошибка импорта';
    bulk.error = String(msg);
  });
};


};
