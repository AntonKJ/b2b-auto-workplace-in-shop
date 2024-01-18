'use strict'

angular.module 'app'
.controller 'app.from-yandex-region.ctrl', [

	'$scope'

	($scope) ->

		$scope.selected = null
		$scope.map = null

		$scope.mapParams =
			center: [50, 30]
			zoom: 3

		$scope.onMapInit = (map) ->

			$scope.map = map
			regionLoadingParams = {
				lang: 'ru'
				quality: 3
			}

			ymaps.regions.load 'RU', regionLoadingParams
				.then handleRegionsLoaded

			return

		prepareSlava = (data) ->

			out = []
			for p in data
				out.push [p[1], p[0]].join ','

			out.join "\n"

		handleRegionsLoaded = (data) ->

			data.geoObjects.events.add 'click', (e) ->

				region = e.get 'target'

				polygon = [];
				for geocoords in region.geometry.getCoordinates()
					for coords in geocoords
						polygon.push coords

				$scope.$apply () ->
					$scope.selected = {
						id: region.properties.get 'osmId'
						title: region.properties.get 'name'
						polygon: polygon
						polygonSlava: prepareSlava polygon
					}
					return

				return

			$scope.map.geoObjects.add data.geoObjects
			return

		return

]
