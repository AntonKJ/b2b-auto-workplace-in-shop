'use strict'

'use strict'

class IndexController

	@$inject: [
		'$scope'
		'zones_resolve'
	]

	constructor: (@$scope, zones_resolve) ->

		@map = null
		@mapParams =
			center: [50, 30]
			zoom: 3

		@zones = zones_resolve
		@activeZone = null
		return

	$onInit: () ->
		return

	onMapInit: (map) ->

		@map = map

		@stateMonitor = {}

		for zone in @zones when zone.geometry?
			@polygon = new ymaps.Polygon zone.geometry, {
				zone: zone
			}, {
				fillColor: '#00FF00'
				fillOpacity: .3
				strokeColor: '#0000FF'
				strokeWidth: 1
			}

			@stateMonitor[zone.id] = new ymaps.Monitor @polygon.properties
			@stateMonitor[zone.id].add 'changedTimeStamp', (newValue) =>
				console.log 'change', newValue, (@polygon.properties.get 'zone.active', false)
				@polygon.options.set 'strokeColor', {true: '#FF0000', false: '#0000FF'}[!!@polygon.properties.get 'zone.active', false]

#			@polygon.properties.events.add 'change', (e) =>
#				active = @polygon.properties.get 'zone.active', false
#				@polygon.options.set 'strokeColor', {true: '#FF0000', false: '#0000FF'}[!!active]

			@polygon.events.add 'click', @regionClickHandler
			@map.geoObjects.add @polygon

		return

	setActiveZone: (zone) ->
		@activeZone = zone
		for z in @zones
			z.active = z.id == zone.id
		return

	regionClickHandler: (e) =>
		zoneElement = e.get 'target'
		zone = zoneElement.properties.get 'zone'
		zone.active = true
		@$scope.$apply () =>
			@setActiveZone zone

		zoneElement.properties.set 'changedTimeStamp', Date.now()
		return

angular.module 'app'
	.controller 'app.index.ctrl', IndexController
