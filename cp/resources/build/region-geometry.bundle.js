'use strict';
angular.module('app', ['templates', 'ui.router', 'ngResource', 'ngSanitize', 'ngStorage', 'ngAnimate', 'angular-loading-bar', 'rzModule', 'ui.bootstrap', 'ui.select', 'app.tyre', 'app.disk', 'app.cart', 'app.profile', 'app.shops', 'app.common']);

angular.module('app').factory('authInterceptor', [
  '$injector', function($injector) {
    var $q, $rootScope, SessionService;
    $q = $injector.get('$q');
    $rootScope = $injector.get('$rootScope');
    SessionService = $injector.get('SessionService');
    return {
      request: function(config) {
        var user;
        user = SessionService.getUser();
        if (user.token != null) {
          config.headers.Authorization = 'Bearer ' + user.token;
        }
        return config;
      },
      responseError: function(rejection) {
        if (rejection.status === 401) {
          SessionService.deleteUser();
          $rootScope.$state.go('auth.login');
        }
        return $q.reject(rejection);
      }
    };
  }
]);

angular.module('app').config([
  '$stateProvider', '$urlRouterProvider', '$urlMatcherFactoryProvider', '$httpProvider', '$httpParamSerializerJQLikeProvider', '$resourceProvider', 'appConfig', function($stateProvider, $urlRouterProvider, $urlMatcherFactoryProvider, $httpProvider, $httpParamSerializerJQLikeProvider, $resourceProvider, appConfig) {
    $urlMatcherFactoryProvider.strictMode(false);
    $httpProvider.interceptors.push('authInterceptor');
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
    $stateProvider.state('auth', {
      url: '/auth',
      abstract: true,
      template: '<ui-view></ui-view>'
    }).state('auth.login', {
      url: '/login',
      controller: 'ctrl.login',
      templateUrl: 'partials/login.html',
      data: {
        noLogin: true
      }
    }).state('auth.logout', {
      url: '/logout',
      controller: [
        '$scope', 'SessionService', '$state', function($scope, SessionService, $state) {
          SessionService.deleteUser();
          $state.go('app.index');
        }
      ],
      template: '',
      data: {
        noLogin: true
      },
      resolve: {
        logout_resolve: [
          'User', function(User) {
            return User.logout();
          }
        ]
      }
    }).state('app', {
      abstract: true,
      url: '/',
      templateUrl: 'partials/layout.html'
    }).state('app.index', {
      url: '',
      templateUrl: 'partials/catalog.html'
    });
  }
]);

angular.module('app').run([
  '$rootScope', '$state', '$stateParams', '$sce', 'SessionService', '$window', function($rootScope, $state, $stateParams, $sce, SessionService, $window) {
    $rootScope.$state = $state;
    $rootScope.$stateParams = $stateParams;
    $rootScope.user = null;
    $rootScope.$on("$stateChangeError", console.log.bind(console));
    $rootScope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams) {
      SessionService.checkAccess(event, toState, toParams, fromState, fromParams);
    });
    $rootScope.trustAsHtml = function(value) {
      return $sce.trustAsHtml(value);
    };
    $rootScope.$on("$stateChangeSuccess", function(event, currentState, previousState) {
      return $window.scrollTo(0, 0);
    });
  }
]);
