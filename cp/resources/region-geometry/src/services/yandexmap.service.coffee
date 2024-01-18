'use strict'

class YandexMapService

	apiUrl: '//api-maps.yandex.ru/2.1/?lang=ru_RU'

	constructor: ($injector) ->

		@q = $injector.get '$q'
		@script = $injector.get '$script'
		@window = $injector.get '$window'

		@api = undefined
		return

	loadScript: () ->

		if !@loadPromise?
			@loadPromise = @q.all [	@script @apiUrl ]

		@loadPromise
		.then () =>
			@window.ymaps

	ready: ->

		deferred = @q.defer()

		@loadScript()
		.then (r) =>

			@api = r
			@api.ready () =>

				deferred.resolve @api

		deferred.promise

angular.module 'app'

.service 'YandexMapService', [ '$injector', YandexMapService ]

.factory '$script', [

	'$q'
	'$rootScope'

	($q, $rootScope) ->

		loadScript = (path, callback) ->

			el = document.createElement "script"
			el.onload = el.onreadystatechange = ->

				if el.readyState && el.readyState != "complete" && el.readyState != "loaded"
					return

				el.onload = el.onreadystatechange = null
				if angular.isFunction callback
					callback()

				return

			el.async = true;
			el.src = path;

			document.getElementsByTagName('body')[0].appendChild el
			return

		loadHistory = []
		pendingPromises = {}

		return (url) ->

			deferred = $q.defer()

			if loadHistory.indexOf(url) != -1
				deferred.resolve()

			else if pendingPromises[url]?
				return pendingPromises[url]

			else

				loadScript url, () ->

					delete pendingPromises[url]
					loadHistory.push url

					$rootScope.$apply () ->
						deferred.resolve()

					return

				pendingPromises[url] = deferred.promise

			deferred.promise
]