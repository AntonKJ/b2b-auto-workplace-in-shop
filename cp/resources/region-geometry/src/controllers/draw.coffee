'use strict'

class DrawController

	@$inject: [
		'$scope'
		'$uibModal'
	]

	constructor: (@$scope, @$uibModal) ->

		@map = null
		@mapParams =
			center: [50, 30]
			zoom: 3

		@polygon = null
		@polygonData = null

		@deliveryZoneId = null
		@mysqlUpdateCommand = null

		return

	$onInit: () ->

		return

	onMapInit: (map) ->

		@map = map

		@polygon = new ymaps.Polygon [], {}, {
			editorDrawingCursor: 'crosshair'
			fillColor: '#00FF00'
			fillOpacity: .5
			strokeColor: '#0000FF'
			strokeWidth: 3
			draggable: true
		}

		@map.geoObjects.add @polygon

		@polygon.events.add 'click', @regionClickHandler

		stateMonitor = new ymaps.Monitor @polygon.editor.state
		stateMonitor.add 'drawing', (newValue) =>
			@polygon.options.set 'strokeColor', (if newValue then '#FF0000' else '#0000FF')

		@polygon.editor.startDrawing()

		return

	prepareSlava: (data) ->

		out = []
		for p in data
			out.push [p[1], p[0]].join ','

		return out.join "\n"

	regionClickHandler: (e) =>

		region = e.get 'target'
		@updateData region.geometry.getCoordinates()

		return

	updateData: (data) ->

		polygon = [];
		for geocoords in data
			for coords in geocoords
				polygon.push coords

		@$scope.$apply () =>

			@polygonData = {
				polygon: data
				polygonSlava: @prepareSlava polygon
			}
			return

		return

	openModal: () ->

		@$uibModal.open {
			controller: 'app.draw.modal.ctrl'
			controllerAs: 'ctrlModal'
			templateUrl: 'partials/draw.modal.html'
		}
		.result
		.then (data) =>

			data = data.trim()

			if data.length == 0
				return

			data = data.split "\n"
			for row, i in data

				row = row.trim().split ','
				data[i] = [ parseFloat(row[1]), parseFloat(row[0]) ]

			if data.length > 0

				if @polygon.editor.state.get 'drawing'
					@polygon.editor.stopDrawing()

				@polygon.geometry.setCoordinates [data]

			return

		return

	doUpdateMysqlCommand: () ->

		if !@polygonData?
			return

		preparedPolygon = []
		preparedPolygonSlava = @polygonData.polygonSlava

		for polygon in @polygonData.polygon

			pData = []
			for point in polygon
				pData.push point.join ' '

			preparedPolygon.push pData

		sql = [
			"""
UPDATE `delivery_zone` SET `Coords` = "#{preparedPolygonSlava}" WHERE `id`=#{@deliveryZoneId};
"""
			"""
UPDATE `delivery_zone` SET `delivery_area` = ST_PolygonFromText('POLYGON((#{preparedPolygon.join '),('}))') WHERE `id`=#{@deliveryZoneId};
"""
		]

		@mysqlUpdateCommand = sql.join "\n"
		return


angular.module 'app'
	.controller 'app.draw.ctrl', DrawController
