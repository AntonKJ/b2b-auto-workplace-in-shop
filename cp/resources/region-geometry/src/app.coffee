'use strict'

angular.module 'app', [

	'templates'
	'ui.router'

	'ngResource'
	'ngSanitize'

	'angular-loading-bar'
	'ui.bootstrap'

]

angular.module('app').constant 'appConfig', appConfig || {}
angular.module('app').constant 'csrfToken', appConfig.csrfToken || {}
angular.module('app').constant 'apiUrl', appConfig.apiUrl || {}

angular.module('app').config [

	'$stateProvider'
	'$urlRouterProvider'
	'$urlMatcherFactoryProvider'

	'$httpProvider'
	'$httpParamSerializerJQLikeProvider'

	'$resourceProvider'

	'appConfig'

	($stateProvider, $urlRouterProvider, $urlMatcherFactoryProvider, $httpProvider, $httpParamSerializerJQLikeProvider, $resourceProvider, appConfig) ->

		$urlMatcherFactoryProvider.strictMode false

		$httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
		$httpProvider.defaults.headers.common['Accept'] = 'application/json, text/javascript'
		$httpProvider.defaults.headers.common['Content-Type'] = 'application/json; charset=utf-8'

		$httpProvider.defaults.headers.post['X-CSRF-Token'] = appConfig.csrfToken
		$httpProvider.defaults.headers.put['X-CSRF-Token'] = appConfig.csrfToken
		$httpProvider.defaults.headers['delete'] = { 'X-CSRF-Token': appConfig.csrfToken }

		$httpProvider.defaults.paramSerializer = $httpParamSerializerJQLikeProvider.$get();

		$resourceProvider.defaults.stripTrailingSlashes = false

		$urlRouterProvider.otherwise '/'

		$stateProvider

    .state 'app', {
      abstract: true
      url: '/'
			template: '<ui-view>'
    }

		.state 'app.index', {
			url: ''
			controller: 'app.index.ctrl'
			controllerAs: 'ctrl'
			templateUrl: 'partials/index.html'
			resolve:
				zones_resolve: [
					'Zones'
					(Zones) ->
						Zones.query()
				]
		}

		.state 'app.from-yandex-region', {
			url: 'from-yandex-region'
			controller: 'app.from-yandex-region.ctrl'
			templateUrl: 'partials/from-yandex-region.html'
		}

		.state 'app.draw', {
			url: ''
			controller: 'app.draw.ctrl'
			controllerAs: 'ctrl'
			templateUrl: 'partials/draw.html'
		}

		return

]

angular.module('app').run [

	'$rootScope'
	'$state'
	'$stateParams'
	'$sce'
	'$window'

	($rootScope, $state, $stateParams, $sce, $window) ->

		$rootScope.$state = $state
		$rootScope.$stateParams = $stateParams

		$rootScope.trustAsHtml = (value) ->
			$sce.trustAsHtml value

		$rootScope.$on "$stateChangeSuccess", (event, currentState, previousState) ->
			$window.scrollTo 0, 0

		return

]
