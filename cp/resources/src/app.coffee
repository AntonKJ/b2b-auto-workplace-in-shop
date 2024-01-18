'use strict'

angular.module 'app', [


	'templates'
	'ui.router'

	'ngResource'
	'ngSanitize'
	'ngStorage'
	'ngAnimate'

	'angular-loading-bar'
	'rzModule'
	'ui.bootstrap'

	'ui.select'

	'app.tyre'
	'app.disk'
	'app.cart'
	'app.profile'
	'app.shops'
	'app.common'

]

angular.module('app')
.factory 'authInterceptor', [

	'$injector'

	($injector) ->

		$q = $injector.get '$q'
		$rootScope = $injector.get '$rootScope'
		SessionService = $injector.get 'SessionService'

		{

			request: (config) ->

				user = SessionService.getUser()

				if user.token?
					config.headers.Authorization = 'Bearer ' + user.token

				return config

			responseError: (rejection) ->

				if rejection.status == 401

					SessionService.deleteUser()
					$rootScope.$state.go 'auth.login'

				$q.reject rejection
		}
]

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

		$httpProvider.interceptors.push 'authInterceptor'

		$httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
		$httpProvider.defaults.headers.common['Accept'] = 'application/json, text/javascript'
		$httpProvider.defaults.headers.common['Content-Type'] = 'application/json; charset=utf-8'

		$httpProvider.defaults.headers.post['X-CSRF-Token'] = appConfig.csrfToken
		$httpProvider.defaults.headers.put['X-CSRF-Token'] = appConfig.csrfToken
		$httpProvider.defaults.headers['delete'] = { 'X-CSRF-Token': appConfig.csrfToken }

		#$httpProvider.defaults.transformRequest.unshift $httpParamSerializerJQLikeProvider.$get()
		$httpProvider.defaults.paramSerializer = $httpParamSerializerJQLikeProvider.$get();

		$resourceProvider.defaults.stripTrailingSlashes = false

		$urlRouterProvider.otherwise '/'

		$stateProvider

		.state 'auth', {
			url: '/auth'
			abstract: true
			template: '<ui-view></ui-view>'
		}

    .state 'auth.login', {
      url: '/login'
      controller: 'ctrl.login'
      templateUrl: 'partials/login.html'
      data:
        noLogin: true
    }

    .state 'auth.logout', {
      url: '/logout'
      controller: [
        '$scope'
        'SessionService'
        '$state'
        ($scope, SessionService, $state) ->
          SessionService.deleteUser()
          $state.go 'app.index'
          return
      ]
      template: ''
			data:
				noLogin: true
			resolve:
				logout_resolve: [
					'User'
					(User) ->
						User.logout()
				]
    }

    .state 'app', {
      abstract: true
      url: '/'
      templateUrl: 'partials/layout.html'
    }

		.state 'app.index', {
			url: ''
			templateUrl: 'partials/catalog.html'
		}

		return
]

angular.module('app').run [

	'$rootScope'
	'$state'
	'$stateParams'
	'$sce'
	'SessionService'
	'$window'

	($rootScope, $state, $stateParams, $sce, SessionService, $window) ->

		$rootScope.$state = $state
		$rootScope.$stateParams = $stateParams

		# Это пользователь
		$rootScope.user = null

		$rootScope.$on "$stateChangeError", console.log.bind console

		# Здесь мы будем проверять авторизацию
		$rootScope.$on '$stateChangeStart', (event, toState, toParams, fromState, fromParams) ->

			SessionService.checkAccess(event, toState, toParams, fromState, fromParams)
			return

		$rootScope.trustAsHtml = (value) ->
			$sce.trustAsHtml value

		$rootScope.$on "$stateChangeSuccess", (event, currentState, previousState) ->
			$window.scrollTo(0, 0)

		return

]