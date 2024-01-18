'use strict';
angular.module('app', ['templates', 'ui.router', 'ngResource', 'ngSanitize', 'angular-loading-bar', 'ui.bootstrap']);

angular.module('app').constant('appConfig', appConfig || {});

angular.module('app').constant('csrfToken', appConfig.csrfToken || {});

angular.module('app').constant('apiUrl', appConfig.apiUrl || {});

angular.module('app').config([
  '$stateProvider', '$urlRouterProvider', '$urlMatcherFactoryProvider', '$httpProvider', '$httpParamSerializerJQLikeProvider', '$resourceProvider', 'appConfig', function($stateProvider, $urlRouterProvider, $urlMatcherFactoryProvider, $httpProvider, $httpParamSerializerJQLikeProvider, $resourceProvider, appConfig) {
    $urlMatcherFactoryProvider.strictMode(false);
    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    $httpProvider.defaults.headers.common['Accept'] = 'application/json, text/javascript';
    $httpProvider.defaults.headers.common['Content-Type'] = 'application/json; charset=utf-8';
    $httpProvider.defaults.headers.post['X-CSRF-Token'] = appConfig.csrfToken;
    $httpProvider.defaults.headers.put['X-CSRF-Token'] = appConfig.csrfToken;
    $httpProvider.defaults.headers['delete'] = {
      'X-CSRF-Token': appConfig.csrfToken
    };
    $httpProvider.defaults.paramSerializer = $httpParamSerializerJQLikeProvider.$get();
    $resourceProvider.defaults.stripTrailingSlashes = false;
    $urlRouterProvider.otherwise('/');
    $stateProvider.state('app', {
      abstract: true,
      url: '/',
      template: '<ui-view>'
    }).state('app.index', {
      url: '',
      controller: 'app.index.ctrl',
      controllerAs: 'ctrl',
      templateUrl: 'partials/index.html',
      resolve: {
        zones_resolve: [
          'Zones', function(Zones) {
            return Zones.query();
          }
        ]
      }
    }).state('app.from-yandex-region', {
      url: 'from-yandex-region',
      controller: 'app.from-yandex-region.ctrl',
      templateUrl: 'partials/from-yandex-region.html'
    }).state('app.draw', {
      url: '',
      controller: 'app.draw.ctrl',
      controllerAs: 'ctrl',
      templateUrl: 'partials/draw.html'
    });
  }
]);

angular.module('app').run([
  '$rootScope', '$state', '$stateParams', '$sce', '$window', function($rootScope, $state, $stateParams, $sce, $window) {
    $rootScope.$state = $state;
    $rootScope.$stateParams = $stateParams;
    $rootScope.trustAsHtml = function(value) {
      return $sce.trustAsHtml(value);
    };
    $rootScope.$on("$stateChangeSuccess", function(event, currentState, previousState) {
      return $window.scrollTo(0, 0);
    });
  }
]);

"use strict";
angular.module('app').directive('yandexMap', [
  function() {
    return {
      restrict: 'EA',
      scope: {
        initParams: '=',
        onAfterInit: '&'
      },
      controller: [
        '$scope', '$element', 'YandexMapService', function($scope, $element, YandexMapService) {
          $scope.map = null;
          YandexMapService.ready().then(function(ym) {
            $scope.map = new ym.Map($element[0], $scope.initParams, {});
            $scope.onAfterInit({
              $target: $scope.map
            });
          });
          $scope.$on('$destroy', function() {
            if ($scope.map) {
              $scope.map.destroy();
            }
          });
        }
      ]
    };
  }
]);

'use strict';
var DrawController,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

DrawController = (function() {
  DrawController.$inject = ['$scope', '$uibModal'];

  function DrawController($scope, $uibModal) {
    this.$scope = $scope;
    this.$uibModal = $uibModal;
    this.regionClickHandler = bind(this.regionClickHandler, this);
    this.map = null;
    this.mapParams = {
      center: [50, 30],
      zoom: 3
    };
    this.polygon = null;
    this.polygonData = null;
    this.deliveryZoneId = null;
    this.mysqlUpdateCommand = null;
    return;
  }

  DrawController.prototype.$onInit = function() {};

  DrawController.prototype.onMapInit = function(map) {
    var stateMonitor;
    this.map = map;
    this.polygon = new ymaps.Polygon([], {}, {
      editorDrawingCursor: 'crosshair',
      fillColor: '#00FF00',
      fillOpacity: .5,
      strokeColor: '#0000FF',
      strokeWidth: 3,
      draggable: true
    });
    this.map.geoObjects.add(this.polygon);
    this.polygon.events.add('click', this.regionClickHandler);
    stateMonitor = new ymaps.Monitor(this.polygon.editor.state);
    stateMonitor.add('drawing', (function(_this) {
      return function(newValue) {
        return _this.polygon.options.set('strokeColor', (newValue ? '#FF0000' : '#0000FF'));
      };
    })(this));
    this.polygon.editor.startDrawing();
  };

  DrawController.prototype.prepareSlava = function(data) {
    var j, len, out, p;
    out = [];
    for (j = 0, len = data.length; j < len; j++) {
      p = data[j];
      out.push([p[1], p[0]].join(','));
    }
    return out.join("\n");
  };

  DrawController.prototype.regionClickHandler = function(e) {
    var region;
    region = e.get('target');
    this.updateData(region.geometry.getCoordinates());
  };

  DrawController.prototype.updateData = function(data) {
    var coords, geocoords, j, k, len, len1, polygon;
    polygon = [];
    for (j = 0, len = data.length; j < len; j++) {
      geocoords = data[j];
      for (k = 0, len1 = geocoords.length; k < len1; k++) {
        coords = geocoords[k];
        polygon.push(coords);
      }
    }
    this.$scope.$apply((function(_this) {
      return function() {
        _this.polygonData = {
          polygon: data,
          polygonSlava: _this.prepareSlava(polygon)
        };
      };
    })(this));
  };

  DrawController.prototype.openModal = function() {
    this.$uibModal.open({
      controller: 'app.draw.modal.ctrl',
      controllerAs: 'ctrlModal',
      templateUrl: 'partials/draw.modal.html'
    }).result.then((function(_this) {
      return function(data) {
        var i, j, len, row;
        data = data.trim();
        if (data.length === 0) {
          return;
        }
        data = data.split("\n");
        for (i = j = 0, len = data.length; j < len; i = ++j) {
          row = data[i];
          row = row.trim().split(',');
          data[i] = [parseFloat(row[1]), parseFloat(row[0])];
        }
        if (data.length > 0) {
          if (_this.polygon.editor.state.get('drawing')) {
            _this.polygon.editor.stopDrawing();
          }
          _this.polygon.geometry.setCoordinates([data]);
        }
      };
    })(this));
  };

  DrawController.prototype.doUpdateMysqlCommand = function() {
    var j, k, len, len1, pData, point, polygon, preparedPolygon, preparedPolygonSlava, ref, sql;
    if (this.polygonData == null) {
      return;
    }
    preparedPolygon = [];
    preparedPolygonSlava = this.polygonData.polygonSlava;
    ref = this.polygonData.polygon;
    for (j = 0, len = ref.length; j < len; j++) {
      polygon = ref[j];
      pData = [];
      for (k = 0, len1 = polygon.length; k < len1; k++) {
        point = polygon[k];
        pData.push(point.join(' '));
      }
      preparedPolygon.push(pData);
    }
    sql = ["UPDATE `delivery_zone` SET `Coords` = \"" + preparedPolygonSlava + "\" WHERE `id`=" + this.deliveryZoneId + ";", "UPDATE `delivery_zone` SET `delivery_area` = ST_PolygonFromText('POLYGON((" + (preparedPolygon.join('),(')) + "))') WHERE `id`=" + this.deliveryZoneId + ";"];
    this.mysqlUpdateCommand = sql.join("\n");
  };

  return DrawController;

})();

angular.module('app').controller('app.draw.ctrl', DrawController);

'use strict';
var DrawModalController;

DrawModalController = (function() {
  DrawModalController.$inject = ['$uibModalInstance'];

  function DrawModalController($uibModalInstance) {
    this.$uibModalInstance = $uibModalInstance;
    this.data = null;
    return;
  }

  DrawModalController.prototype.ok = function() {
    this.$uibModalInstance.close(this.data);
  };

  DrawModalController.prototype.cancel = function() {
    this.$uibModalInstance.dismiss(false);
  };

  return DrawModalController;

})();

angular.module('app').controller('app.draw.modal.ctrl', DrawModalController);

'use strict';
angular.module('app').controller('app.from-yandex-region.ctrl', [
  '$scope', function($scope) {
    var handleRegionsLoaded, prepareSlava;
    $scope.selected = null;
    $scope.map = null;
    $scope.mapParams = {
      center: [50, 30],
      zoom: 3
    };
    $scope.onMapInit = function(map) {
      var regionLoadingParams;
      $scope.map = map;
      regionLoadingParams = {
        lang: 'ru',
        quality: 3
      };
      ymaps.regions.load('RU', regionLoadingParams).then(handleRegionsLoaded);
    };
    prepareSlava = function(data) {
      var i, len, out, p;
      out = [];
      for (i = 0, len = data.length; i < len; i++) {
        p = data[i];
        out.push([p[1], p[0]].join(','));
      }
      return out.join("\n");
    };
    handleRegionsLoaded = function(data) {
      data.geoObjects.events.add('click', function(e) {
        var coords, geocoords, i, j, len, len1, polygon, ref, region;
        region = e.get('target');
        polygon = [];
        ref = region.geometry.getCoordinates();
        for (i = 0, len = ref.length; i < len; i++) {
          geocoords = ref[i];
          for (j = 0, len1 = geocoords.length; j < len1; j++) {
            coords = geocoords[j];
            polygon.push(coords);
          }
        }
        $scope.$apply(function() {
          $scope.selected = {
            id: region.properties.get('osmId'),
            title: region.properties.get('name'),
            polygon: polygon,
            polygonSlava: prepareSlava(polygon)
          };
        });
      });
      $scope.map.geoObjects.add(data.geoObjects);
    };
  }
]);

'use strict';
'use strict';
var IndexController,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

IndexController = (function() {
  IndexController.$inject = ['$scope', 'zones_resolve'];

  function IndexController($scope, zones_resolve) {
    this.$scope = $scope;
    this.regionClickHandler = bind(this.regionClickHandler, this);
    this.map = null;
    this.mapParams = {
      center: [50, 30],
      zoom: 3
    };
    this.zones = zones_resolve;
    this.activeZone = null;
    return;
  }

  IndexController.prototype.$onInit = function() {};

  IndexController.prototype.onMapInit = function(map) {
    var i, len, ref, zone;
    this.map = map;
    this.stateMonitor = {};
    ref = this.zones;
    for (i = 0, len = ref.length; i < len; i++) {
      zone = ref[i];
      if (!(zone.geometry != null)) {
        continue;
      }
      this.polygon = new ymaps.Polygon(zone.geometry, {
        zone: zone
      }, {
        fillColor: '#00FF00',
        fillOpacity: .3,
        strokeColor: '#0000FF',
        strokeWidth: 1
      });
      this.stateMonitor[zone.id] = new ymaps.Monitor(this.polygon.properties);
      this.stateMonitor[zone.id].add('changedTimeStamp', (function(_this) {
        return function(newValue) {
          console.log('change', newValue, _this.polygon.properties.get('zone.active', false));
          return _this.polygon.options.set('strokeColor', {
            "true": '#FF0000',
            "false": '#0000FF'
          }[!!_this.polygon.properties.get('zone.active', false)]);
        };
      })(this));
      this.polygon.events.add('click', this.regionClickHandler);
      this.map.geoObjects.add(this.polygon);
    }
  };

  IndexController.prototype.setActiveZone = function(zone) {
    var i, len, ref, z;
    this.activeZone = zone;
    ref = this.zones;
    for (i = 0, len = ref.length; i < len; i++) {
      z = ref[i];
      z.active = z.id === zone.id;
    }
  };

  IndexController.prototype.regionClickHandler = function(e) {
    var zone, zoneElement;
    zoneElement = e.get('target');
    zone = zoneElement.properties.get('zone');
    zone.active = true;
    this.$scope.$apply((function(_this) {
      return function() {
        return _this.setActiveZone(zone);
      };
    })(this));
    zoneElement.properties.set('changedTimeStamp', Date.now());
  };

  return IndexController;

})();

angular.module('app').controller('app.index.ctrl', IndexController);

'use strict';
var YandexMapService;

YandexMapService = (function() {
  YandexMapService.prototype.apiUrl = '//api-maps.yandex.ru/2.1/?lang=ru_RU';

  function YandexMapService($injector) {
    this.q = $injector.get('$q');
    this.script = $injector.get('$script');
    this.window = $injector.get('$window');
    this.api = void 0;
    return;
  }

  YandexMapService.prototype.loadScript = function() {
    if (this.loadPromise == null) {
      this.loadPromise = this.q.all([this.script(this.apiUrl)]);
    }
    return this.loadPromise.then((function(_this) {
      return function() {
        return _this.window.ymaps;
      };
    })(this));
  };

  YandexMapService.prototype.ready = function() {
    var deferred;
    deferred = this.q.defer();
    this.loadScript().then((function(_this) {
      return function(r) {
        _this.api = r;
        return _this.api.ready(function() {
          return deferred.resolve(_this.api);
        });
      };
    })(this));
    return deferred.promise;
  };

  return YandexMapService;

})();

angular.module('app').service('YandexMapService', ['$injector', YandexMapService]).factory('$script', [
  '$q', '$rootScope', function($q, $rootScope) {
    var loadHistory, loadScript, pendingPromises;
    loadScript = function(path, callback) {
      var el;
      el = document.createElement("script");
      el.onload = el.onreadystatechange = function() {
        if (el.readyState && el.readyState !== "complete" && el.readyState !== "loaded") {
          return;
        }
        el.onload = el.onreadystatechange = null;
        if (angular.isFunction(callback)) {
          callback();
        }
      };
      el.async = true;
      el.src = path;
      document.getElementsByTagName('body')[0].appendChild(el);
    };
    loadHistory = [];
    pendingPromises = {};
    return function(url) {
      var deferred;
      deferred = $q.defer();
      if (loadHistory.indexOf(url) !== -1) {
        deferred.resolve();
      } else if (pendingPromises[url] != null) {
        return pendingPromises[url];
      } else {
        loadScript(url, function() {
          delete pendingPromises[url];
          loadHistory.push(url);
          $rootScope.$apply(function() {
            return deferred.resolve();
          });
        });
        pendingPromises[url] = deferred.promise;
      }
      return deferred.promise;
    };
  }
]);

"use strict";
angular.module('app').factory('ZonesResource', [
  '$resource', 'apiUrl', function($resource, apiUrl) {
    return $resource(apiUrl + "zones/:action/:id/.json", {
      action: '@action',
      id: '@id'
    }, {
      query: {
        cache: false,
        method: 'GET',
        isArray: true,
        responseType: 'json'
      }
    });
  }
]);

angular.module('app').service('Zones', [
  'ZonesResource', function(ZonesResource) {
    return {
      query: function() {
        return ZonesResource.query({}).$promise;
      }
    };
  }
]);

//HEAD 
(function(app) {
try { app = angular.module("templates"); }
catch(err) { app = angular.module("templates", []); }
app.run(["$templateCache", function($templateCache) {
"use strict";

$templateCache.put("partials/draw.html","<a class=\"btn btn-default\" ui-sref=\"app.from-yandex-region\">Выбрать из регионов яндекса</a>\n" +
    "<a class=\"btn btn-success\" ng-click=\"ctrl.openModal()\">Добавить регион по точкам</a>\n" +
    "<hr>\n" +
    "\n" +
    "<div yandex-map\n" +
    "     style=\"height: 500px;\"\n" +
    "     init-params=\"ctrl.mapParams\"\n" +
    "     on-after-init=\"ctrl.onMapInit($target)\"></div>\n" +
    "\n" +
    "<div ng-if=\"ctrl.polygonData != null\">\n" +
    "\n" +
    "	<h3>Кол-во точек: {{ctrl.polygonData.polygon.length}}</h3>\n" +
    "	<textarea rows=\"10\" style=\"box-sizing: border-box; width: 100%;\">{{ctrl.polygonData.polygon}}</textarea>\n" +
    "\n" +
    "	<h3>Длина строки: {{ctrl.polygonData.polygonSlava.length}} &mdash; Слава</h3>\n" +
    "	<textarea rows=\"10\" style=\"box-sizing: border-box; width: 100%;\">{{ctrl.polygonData.polygonSlava}}</textarea>\n" +
    "\n" +
    "	<h3>Запрос для обновления</h3>\n" +
    "\n" +
    "	<div class=\"form-row\">\n" +
    "		<label for=\"delivery_zone_id\">ID для обновления `delivery_zone`</label>\n" +
    "		<input id=\"delivery_zone_id\"\n" +
    "		       class=\"form-control\"\n" +
    "		       ng-change=\"ctrl.doUpdateMysqlCommand()\"\n" +
    "		       ng-model=\"ctrl.deliveryZoneId\"/>\n" +
    "	</div>\n" +
    "\n" +
    "	<div style=\"margin-top: 1em;\" ng-if=\"ctrl.deliveryZoneId != null && ctrl.deliveryZoneId != ''\">\n" +
    "	<pre>\n" +
    "		{{ctrl.mysqlUpdateCommand}}\n" +
    "	</pre>\n" +
    "	</div>\n" +
    "\n" +
    "</div>\n" +
    "\n" +
    "<hr>\n" +
    "")

$templateCache.put("partials/draw.modal.html","<div class=\"modal-header\">\n" +
    "	<h3 class=\"modal-title\">Вставте список точек в поле</h3>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"modal-body\">\n" +
    "	<textarea class=\"form-control\" ng-model=\"ctrlModal.data\"></textarea>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"modal-footer\">\n" +
    "	<a class=\"btn btn-default pull-left\" href ng-click=\"ctrlModal.cancel()\">Отмена</a>\n" +
    "	<a class=\"btn btn-success\" href ng-click=\"ctrlModal.ok()\">Добавить на карту</a>\n" +
    "</div>")

$templateCache.put("partials/from-yandex-region.html","<a class=\"btn btn-default\" ui-sref=\"app.draw\">Нарисовать свой регион</a>\n" +
    "<hr>\n" +
    "\n" +
    "<div yandex-map\n" +
    "     style=\"height: 300px;\"\n" +
    "     init-params=\"mapParams\"\n" +
    "     on-after-init=\"onMapInit($target)\"\n" +
    "></div>\n" +
    "\n" +
    "<div ng-if=\"selected != null\">\n" +
    "\n" +
    "	<h3>{{selected.title}} [#{{selected.id}}]</h3>\n" +
    "	<textarea rows=\"10\" style=\"box-sizing: border-box; width: 100%;\">{{selected.polygon}}</textarea>\n" +
    "\n" +
    "	<h3>{{selected.title}} [#{{selected.id}}] &mdash; Слава</h3>\n" +
    "	<textarea rows=\"10\" style=\"box-sizing: border-box; width: 100%;\">{{selected.polygonSlava}}</textarea>\n" +
    "\n" +
    "</div>")

$templateCache.put("partials/index.html","<div class=\"index-page\">\n" +
    "	<div class=\"map-wrapper\">\n" +
    "		<div yandex-map\n" +
    "		     style=\"height: 500px;\"\n" +
    "		     init-params=\"ctrl.mapParams\"\n" +
    "		     on-after-init=\"ctrl.onMapInit($target)\"></div>\n" +
    "	</div>\n" +
    "	<div class=\"zones-list\">\n" +
    "		<div class=\"table-wrapper\">\n" +
    "			<table>\n" +
    "				<tbody>\n" +
    "				<tr ng-repeat=\"zone in ctrl.zones track by zone.id\"\n" +
    "				    ng-style=\"{true:{background: '#eaeaea'},false:{}}[zone.active]\">\n" +
    "					<td class=\"text-center\">{{zone.id}}</td>\n" +
    "					<td><a href ng-click=\"ctrl.setActiveZone(zone)\">{{zone.title}}</a></td>\n" +
    "					<td class=\"text-center\">{{zone.order_type_id}}</td>\n" +
    "				</tr>\n" +
    "				</tbody>\n" +
    "			</table>\n" +
    "		</div>\n" +
    "	</div>\n" +
    "</div>\n" +
    "")
}]);
})();